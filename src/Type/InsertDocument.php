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

use ArangoDb\Http\VpackStream;
use ArangoDb\Url;
use Fig\Http\Message\RequestMethodInterface;
use ArangoDb\Http\Request;
use Iterator;
use Psr\Http\Message\RequestInterface;

final class InsertDocument implements CollectionType
{
    /**
     * @var string
     */
    private $collectionName;

    /**
     * @var array
     */
    private $streamEvents;

    /**
     * @var array
     */
    private $options;

    private function __construct(string $collectionName, array $streamEvents, array $options = [])
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
     * @param array $docs
     * @param array $options
     * @return InsertDocument
     */
    public static function with(string $collectionName, array $docs, array $options = []): InsertDocument
    {
        return new self($collectionName, $docs, $options);
    }

    public function collectionName(): string
    {
        return $this->collectionName;
    }

    public function toRequest(): RequestInterface
    {
        return new Request(
            RequestMethodInterface::METHOD_POST,
            Url::DOCUMENT . '/' . $this->collectionName . '?' . http_build_query($this->options),
            [],
            new VpackStream($this->streamEvents)
        );
    }

    public function toJs(): string
    {
        return 'var rId = db.' . $this->collectionName
            . '.insert(' . json_encode($this->streamEvents) . ', ' . json_encode($this->options) . ');';
    }

    public function count(): int
    {
        return count($this->streamEvents);
    }
}
