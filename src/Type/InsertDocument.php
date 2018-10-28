<?php
/**
 * Sandro Keil (https://sandro-keil.de)
 *
 * @link      http://github.com/sandrokeil/arangodb-php-client for the canonical source repository
 * @copyright Copyright (c) 2018 Sandro Keil
 * @license   http://github.com/sandrokeil/arangodb-php-client/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ArangoDb\Type;

use ArangoDb\VpackStream;
use ArangoDBClient\Urls;
use Fig\Http\Message\RequestMethodInterface;
use GuzzleHttp\Psr7\Request;
use Iterator;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class InsertDocument implements CollectionType
{
    /**
     * @var string
     */
    private $collectionName;

    /**
     * @var iterable
     */
    private $streamEvents;

    /**
     * @var array
     */
    private $options;

    private function __construct(string $collectionName, iterable $streamEvents, array $options = [])
    {
        $this->collectionName = $collectionName;
        $this->streamEvents = $streamEvents;
        $this->options = $options;
    }

    /**
     * @see https://docs.arangodb.com/3.3/Manual/DataModeling/Documents/DocumentMethods.html#insert
     * @see https://docs.arangodb.com/3.3/HTTP/Document/WorkingWithDocuments.html#create-document
     *
     * @param string $collectionName
     * @param iterable $docs
     * @param array $options
     * @return InsertDocument
     */
    public static function with(string $collectionName, iterable $docs, array $options = []): InsertDocument
    {
        return new self($collectionName, $docs, $options);
    }

    public function collectionName(): string
    {
        return $this->collectionName;
    }

    public function checkResponse(ResponseInterface $response, string $rId = null): ?int
    {
        return ($this->inspector)($response, $rId);
    }

    public function toRequest(): RequestInterface
    {
        return new Request(
            RequestMethodInterface::METHOD_POST,
            Urls::URL_DOCUMENT . '/' . $this->collectionName . '/?' . http_build_query($this->options),
            [],
            new VpackStream($this->streamEvents)
        );
    }

    public function toJs(): string
    {
        if (method_exists($this->streamEvents, 'asJson') === true) {
            return 'var rId = db.' . $this->collectionName
                . '.insert(' . $this->streamEvents->asJson() . ', ' . json_encode($this->options) . ');';
        }

        return 'var rId = db.' . $this->collectionName
            . '.insert(' . json_encode($this->streamEvents) . ', ' . json_encode($this->options) . ');';
    }

    public function count(): int
    {
        return $this->streamEvents instanceof Iterator
            ? iterator_count($this->streamEvents)
            : count($this->streamEvents);
    }
}
