<?php
/**
 * Sandro Keil (https://sandro-keil.de)
 *
 * @link      http://github.com/sandrokeil/arangodb-php-client for the canonical source repository
 * @copyright Copyright (c) 2018 Sandro Keil
 * @license   http://github.com/sandrokeil/arangodb-php-client/blob/master/LICENSE.md New BSD License
 */

namespace ArangoDb;

use ArangoDb\Type\CreateCursor;
use ArangoDBClient\Urls;
use Countable;
use Fig\Http\Message\RequestMethodInterface;
use GuzzleHttp\Psr7\Request;
use Iterator;
use Velocypack\Vpack;

class Statement implements Iterator, Countable
{
    /**
     * result entry for cursor id
     */
    public const ENTRY_ID = 'id';

    /**
     * result entry for "hasMore" flag
     */
    public const ENTRY_HASMORE = 'hasMore';

    /**
     * result entry for result documents
     */
    public const ENTRY_RESULT = 'result';

    /**
     * result entry for extra data
     */
    public const ENTRY_EXTRA = 'extra';

    /**
     * result entry for stats
     */
    public const ENTRY_STATS = 'stats';

    /**
     * result entry for the full count (ignoring the outermost LIMIT)
     */
    public const FULL_COUNT = 'fullCount';

    /**
     * cache option entry
     */
    public const ENTRY_CACHE = 'cache';

    /**
     * cached result attribute - whether or not the result was served from the AQL query cache
     */
    public const ENTRY_CACHED = 'cached';

    /**
     * sanitize option entry
     */
    public const ENTRY_SANITIZE = 'sanitize';

    /**
     * "objectType" option entry.
     */
    public const ENTRY_TYPE = 'objectType';

    public const ENTRY_TYPE_JSON = 'json';
    public const ENTRY_TYPE_ARRAY = 'array';
    public const ENTRY_TYPE_OBJECT = 'object';

    /**
     * The connection object
     *
     * @var Client
     */
    private $client;
    /**
     * Cursor options
     *
     * @var array
     */
    private $options;

    /**
     * Result Data
     *
     * @var Vpack
     */
    private $data;

    /**
     * "has more" indicator - if true, the server has more results
     *
     * @var bool
     */
    private $hasMore = true;

    /**
     * cursor id - might be NULL if cursor does not have an id
     *
     * @var string
     */
    private $id;

    /**
     * current position in result set iteration (zero-based)
     *
     * @var int
     */
    private $position;

    /**
     * total length of result set (in number of documents)
     *
     * @var int
     */
    private $length;

    /**
     * full count of the result set (ignoring the outermost LIMIT)
     *
     * @var int
     */
    private $fullCount;

    /**
     * extra data (statistics) returned from the statement
     *
     * @var array
     */
    private $extra;

    /**
     * number of HTTP calls that were made to build the cursor result
     */
    private $fetches = 0;

    /**
     * whether or not the query result was served from the AQL query result cache
     */
    private $cached;

    /**
     * @var CreateCursor
     */
    private $cursor;

    /**
     * @var bool
     */
    private $executed = false;

    /**
     * Initialise the cursor with the first results and some metadata
     *
     * @param Client $client - connection to be used
     * @param CreateCursor $cursor
     * @param array $options
     */
    public function __construct(Client $client, CreateCursor $cursor, array $options = [])
    {
        if (! isset($options[self::ENTRY_TYPE])) {
            $options[self::ENTRY_TYPE] = self::ENTRY_TYPE_JSON;
        }

        $this->client = $client;
        $this->extra = [];
        $this->cached = false;
        $this->options = $options;
        $this->cursor = $cursor;
        $this->data = Vpack::fromArray([]);
    }

    /**
     * Fetch outstanding results from the server
     *
     * @throws Exception
     * @return void
     */
    private function fetchOutstanding(): void
    {
        if ($this->fetches === 0) {
            $response = $this->client->sendRequest(
                $this->cursor->toRequest()
            );
        } else {
            // continuation
            $response = $this->client->sendRequest(
                new Request(
                    RequestMethodInterface::METHOD_PUT,
                    Urls::URL_CURSOR . '/' . $this->id
                )
            );
        }

        ++$this->fetches;

        $data = $response->getBody();

        if ($data instanceof VpackStream) {
            $data = $data->vpack();
        } else {
            $data = Vpack::fromJson($data->getContents());
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
        $this->hasMore = $data[self::ENTRY_HASMORE] ?? false;

        $this->add($data[self::ENTRY_RESULT]);

        if (! $this->hasMore) {
            // we have fetched the complete result set and can unset the id now
            $this->id = null;
        }
    }

    /**
     * Create an array of results from the input array
     *
     * @param array $data - incoming result
     *
     * @return void
     * @throws \ArangoDBClient\ClientException
     */
    private function add($data): void
    {
        $this->length += count($data);
        // TODO remove Vpack::fromArray if append is ready
        $this->data = Vpack::fromArray(array_merge($this->data->toArray(), $data->toArray()));
    }

    /**
     * Get all results as an array
     *
     * This might issue additional HTTP requests to fetch any outstanding
     * results from the server
     *
     * @throws Exception
     * @return mixed - an array of all results
     */
    public function getAll()
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
     * Get the total number of results in the cursor
     *
     * This might issue additional HTTP requests to fetch any outstanding
     * results from the server
     *
     * @throws Exception
     * @return int - total number of results
     */
    public function count()
    {
        while ($this->hasMore) {
            $this->fetchOutstanding();
        }

        return $this->length;
    }

    /**
     * Get the full count of the cursor (ignoring the outermost LIMIT)
     *
     * @return int - total number of results
     */
    public function getFullCount()
    {
        return $this->fullCount;
    }

    /**
     * Get the cached attribute for the result set
     *
     * @return bool - whether or not the query result was served from the AQL query cache
     */
    public function getCached()
    {
        return $this->cached;
    }

    /**
     * Rewind the cursor, necessary for Iterator
     *
     * @return void
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
     * Return the current result row, necessary for Iterator
     *
     * @return mixed - the current result row as an assoc array
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

    /**
     * Return the index of the current result row, necessary for Iterator
     *
     * @return int - the current result row index
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * Advance the cursor, necessary for Iterator
     *
     * @return void
     */
    public function next()
    {
        ++$this->position;
    }

    /**
     * Check if cursor can be advanced further, necessary for Iterator
     *
     * This might issue additional HTTP requests to fetch any outstanding
     * results from the server
     *
     * @throws Exception
     * @return bool - true if the cursor can be advanced further, false if cursor is at end
     */
    public function valid()
    {
        if ($this->position <= $this->length - 1) {
            // we have more results than the current position is
            return true;
        }

        if (! $this->hasMore || ! $this->id) {
            // we don't have more results, but the cursor is exhausted
            return false;
        }

        // need to fetch additional results from the server
        $this->fetchOutstanding();

        return ($this->position <= $this->length - 1);
    }

    /**
     * Get a statistical figure value from the query result
     *
     * @param string $name - name of figure to return
     *
     * @return int
     */
    private function getStatValue($name): int
    {
        return $this->extra[self::ENTRY_STATS][$name] ?? 0;
    }

    /**
     * Get current cursor type
     *
     * @return CreateCursor
     */
    public function getCursor(): CreateCursor
    {
        return $this->cursor;
    }

    /**
     * Return the extra data of the query (statistics etc.). Contents of the result array
     * depend on the type of query executed
     *
     * @return array
     */
    public function getExtra(): array
    {
        return $this->extra;
    }

    /**
     * Return the warnings issued during query execution
     *
     * @return array
     */
    public function getWarnings(): int
    {
        return $this->extra['warnings'] ?? [];
    }

    /**
     * Return the number of writes executed by the query
     *
     * @return int
     */
    public function getWritesExecuted(): int
    {
        return $this->getStatValue('writesExecuted');
    }

    /**
     * Return the number of ignored write operations from the query
     *
     * @return int
     */
    public function getWritesIgnored(): int
    {
        return $this->getStatValue('writesIgnored');
    }

    /**
     * Return the number of documents iterated over in full scans
     *
     * @return int
     */
    public function getScannedFull(): int
    {
        return $this->getStatValue('scannedFull');
    }

    /**
     * Return the number of documents iterated over in index scans
     *
     * @return int
     */
    public function getScannedIndex(): int
    {
        return $this->getStatValue('scannedIndex');
    }

    /**
     * Return the number of documents filtered by the query
     *
     * @return int
     */
    public function getFiltered(): int
    {
        return $this->getStatValue('filtered');
    }

    /**
     * Return the number of HTTP calls that were made to build the cursor result
     *
     * @return int
     */
    public function getFetches(): int
    {
        return $this->fetches;
    }

    /**
     * @return string
     */
    public function getId(): ?string
    {
        return $this->id;
    }
}
