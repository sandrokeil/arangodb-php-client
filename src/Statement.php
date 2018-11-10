<?php
/**
 * Sandro Keil (https://sandro-keil.de)
 *
 * @link      http://github.com/sandrokeil/arangodb-php-client for the canonical source repository
 * @copyright Copyright (c) 2018 Sandro Keil
 * @license   http://github.com/sandrokeil/arangodb-php-client/blob/master/LICENSE.md New BSD License
 */

namespace ArangoDb;

use ArangoDb\Exception\ServerException;
use ArangoDb\Http\VpackStream;
use Countable;
use Fig\Http\Message\RequestMethodInterface;
use Fig\Http\Message\StatusCodeInterface;
use ArangoDb\Http\Request;
use Iterator;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Velocypack\Vpack;

class Statement implements Iterator, Countable
{
    /**
     * "objectType" option entry.
     */
    public const ENTRY_TYPE = 'objectType';

    public const ENTRY_TYPE_JSON = 'json';
    public const ENTRY_TYPE_ARRAY = 'array';
    public const ENTRY_TYPE_OBJECT = 'object';

    /**
     * Entry id for cursor id
     */
    private const ENTRY_ID = 'id';

    /**
     * Whether or not to get more documents
     */
    private const ENTRY_HAS_MORE = 'hasMore';

    /**
     * Result documents
     */
    private const ENTRY_RESULT = 'result';

    /**
     * Extra data
     */
    private const ENTRY_EXTRA = 'extra';

    /**
     * Stats
     */
    private const ENTRY_STATS = 'stats';

    /**
     * Full count (ignoring the outermost LIMIT)
     */
    private const FULL_COUNT = 'fullCount';

    /**
     * Whether or not the result was served from the AQL query cache
     */
    private const ENTRY_CACHED = 'cached';

    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * Cursor options
     *
     * @var array
     */
    private $options;

    /**
     * @var Vpack
     */
    private $data;

    /**
     * @var bool
     */
    private $hasMore = true;

    /**
     * cursor id
     *
     * @var string
     */
    private $id;

    /**
     * Current position in result set iteration (zero-based)
     *
     * @var int
     */
    private $position;

    /**
     * Total length of result set (in number of documents)
     *
     * @var int
     */
    private $length;

    /**
     * Full count of the result set (ignoring the outermost LIMIT)
     *
     * @var int|null
     */
    private $fullCount;

    /**
     * Extra data (statistics) returned from the statement
     *
     * @var array
     */
    private $extra = [];

    /**
     * Number of HTTP calls that were made to build the cursor result
     *
     * @var int
     */
    private $fetches = 0;

    /**
     * Whether or not the query result was served from the AQL query result cache
     *
     * @var bool
     */
    private $cached = false;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var bool
     */
    private $executed = false;

    /**
     * Query is executed on first access
     *
     * @param ClientInterface $client - connection to be used
     * @param RequestInterface $request Cursor request
     * @param array $options
     */
    public function __construct(ClientInterface $client, RequestInterface $request, array $options = [])
    {
        if (! isset($options[self::ENTRY_TYPE])) {
            $options[self::ENTRY_TYPE] = self::ENTRY_TYPE_JSON;
        }

        $this->client = $client;
        $this->extra = [];
        $this->cached = false;
        $this->options = $options;
        $this->request = $request;
        $this->data = Vpack::fromArray([]);
    }

    /**
     * Fetch outstanding results from the server
     *
     * @return void
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    private function fetchOutstanding(): void
    {
        $request = $this->fetches === 0
            ? $this->request
            : new Request(RequestMethodInterface::METHOD_PUT, Url::CURSOR . '/' . $this->id);

        $response = $this->client->sendRequest($request);

        $httpStatusCode = $response->getStatusCode();

        if ($httpStatusCode < StatusCodeInterface::STATUS_OK
            || $httpStatusCode > StatusCodeInterface::STATUS_MULTIPLE_CHOICES
        ) {
            throw ServerException::for($request, $response);
        }

        ++$this->fetches;

        $data = $response->getBody();
        $tmp = $data->getContents();
        if ($data instanceof VpackStream) {
            $data = $data->vpack();
        } else {
            $data = Vpack::fromJson($tmp);
        }

        if (isset($data[self::ENTRY_ID])) {
            $this->id = $data[self::ENTRY_ID];
        }

        if (isset($data[self::ENTRY_EXTRA])) {
            $this->extra = $data[self::ENTRY_EXTRA];

            if (isset($this->extra[self::ENTRY_STATS][self::FULL_COUNT])) {
                $this->fullCount = $this->extra[self::ENTRY_STATS][self::FULL_COUNT];
            }
        }

        if (isset($data[self::ENTRY_CACHED])) {
            $this->cached = $data[self::ENTRY_CACHED];
        }
        $this->hasMore = $data[self::ENTRY_HAS_MORE] ?? false;

        $this->length += count($data[self::ENTRY_RESULT]);
        // TODO remove Vpack::fromArray if append is ready
        $this->data = Vpack::fromArray(array_merge($this->data->toArray(), $data[self::ENTRY_RESULT]->toArray()));

        if (false === $this->hasMore) {
            unset($this->id);
        }
    }

    /**
     * Return the current result row depending on entry type
     *
     * This might issue additional HTTP requests to fetch any outstanding results from the server
     *
     * @return string|array|object Data
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function fetchAll()
    {
        while ($this->hasMore) {
            $this->fetchOutstanding();
        }

        switch ($this->options[self::ENTRY_TYPE]) {
            case self::ENTRY_TYPE_OBJECT:
                return (object)$this->data->toArray();
                break;
            case self::ENTRY_TYPE_ARRAY:
                return $this->data->toArray();
                break;
            case self::ENTRY_TYPE_JSON:
            default:
                return $this->data->toJson();
                break;
        }
    }

    /**
     * Get the total number of results in the cursor.
     *
     * This might issue additional HTTP requests to fetch any outstanding results from the server.
     *
     * @return int Total number of results
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function count()
    {
        while ($this->hasMore) {
            $this->fetchOutstanding();
        }

        return $this->length;
    }

    /**
     * Rewind the cursor, loads first batch, can be repeated (new cursor will be created)
     *
     * @return void
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function rewind()
    {
        $this->length = 0;
        $this->fetches = 0;
        $this->position = 0;
        $this->executed = false;
        $this->hasMore = true;

        $this->data = Vpack::fromArray([]);
        $this->fetchOutstanding();
    }

    /**
     * Return the current result row depending on entry type
     *
     * @return string|array|object Data
     */
    public function current()
    {
        switch ($this->options[self::ENTRY_TYPE]) {
            case self::ENTRY_TYPE_OBJECT:
                return (object)$this->data[$this->position]->toArray();
            case self::ENTRY_TYPE_ARRAY:
                return $this->data[$this->position]->toArray();
            case self::ENTRY_TYPE_JSON:
            default:
                return $this->data[$this->position]->toJson();
        }
    }

    public function key(): int
    {
        return $this->position;
    }

    public function next(): void
    {
        ++$this->position;
    }

    /**
     * @return bool
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function valid(): bool
    {
        if ($this->position <= $this->length - 1) {
            // we have more results than the current position is
            return true;
        }

        if (! $this->hasMore || $this->id === null) {
            return false;
        }

        // need to fetch additional results from the server
        $this->fetchOutstanding();

        return ($this->position <= $this->length - 1);
    }

    /**
     * Returns the extra data of the query (statistics etc.). Contents of the result array depend on the type of query
     * executed
     *
     * @return array
     */
    public function extra(): array
    {
        return $this->extra ?? [];
    }

    /**
     * Returns the warnings issued during query execution
     *
     * @return array
     */
    public function warnings(): array
    {
        return $this->extra['warnings'] ?? [];
    }

    /**
     * Returns the number of writes executed by the query
     *
     * @return int
     */
    public function writesExecuted(): int
    {
        return $this->getStatValue('writesExecuted');
    }

    /**
     * Returns the number of ignored write operations from the query
     *
     * @return int
     */
    public function writesIgnored(): int
    {
        return $this->getStatValue('writesIgnored');
    }

    /**
     * Returns the number of documents iterated over in full scans
     *
     * @return int
     */
    public function scannedFull(): int
    {
        return $this->getStatValue('scannedFull');
    }

    /**
     * Returns the number of documents iterated over in index scans
     *
     * @return int
     */
    public function scannedIndex(): int
    {
        return $this->getStatValue('scannedIndex');
    }

    /**
     * Returns the number of documents filtered by the query
     *
     * @return int
     */
    public function filtered(): int
    {
        return $this->getStatValue('filtered');
    }

    /**
     * Returns the number of HTTP calls that were made to build the cursor result
     *
     * @return int
     */
    public function fetches(): int
    {
        return $this->fetches;
    }

    /**
     * Returns cursor id only after first rewind / fetch
     *
     * @return string
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Get the full count of the cursor if available. Does not load all data.
     *
     * @return int Total number of results
     */
    public function fullCount(): ?int
    {
        return $this->fullCount;
    }

    /**
     * Get the cached attribute for the result set
     *
     * @return bool Whether or not the query result was served from the AQL query cache
     */
    public function isCached(): bool
    {
        return $this->cached;
    }

    /**
     * Returns statistical figure value from the query result, default is 0
     *
     * @param string $name Name of figure
     *
     * @return int
     */
    private function getStatValue(string $name): int
    {
        return $this->extra[self::ENTRY_STATS][$name] ?? 0;
    }
}
