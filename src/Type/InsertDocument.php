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

use ArangoDBClient\HttpHelper;
use ArangoDBClient\Urls;
use Psr\Http\Message\ResponseInterface;

final class InsertDocument implements Type
{
    use ToHttpTrait;

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

    /**
     * Inspects response
     *
     * @var callable
     */
    private $inspector;

    private function __construct(
        string $collectionName,
        iterable $streamEvents,
        array $options = [],
        callable $inspector = null
    ) {
        $this->collectionName = $collectionName;
        $this->streamEvents = $streamEvents;
        $this->options = $options;
        $this->inspector = $inspector ?: function (ResponseInterface $response, string $rId = null) {
            if (null === $rId) {
                return null;
            }

            return strpos($response->getBody(), '"' . $rId . '"' . ':0') !== false
                    || strpos($response->getBody(), '"' . $rId . '"' . ':[{"error":true') !== false
                ? 404 : null;
        };
    }

    /**
     * @see https://docs.arangodb.com/3.2/Manual/DataModeling/Documents/DocumentMethods.html#insert
     * @see https://docs.arangodb.com/3.2/HTTP/Document/WorkingWithDocuments.html#create-document
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

    /**
     * @see https://docs.arangodb.com/3.2/Manual/DataModeling/Documents/DocumentMethods.html#insert
     * @see https://docs.arangodb.com/3.2/HTTP/Document/WorkingWithDocuments.html#create-document
     *
     * @param string $collectionName
     * @param iterable $docs
     * @param callable $inspector Inspects result, signature is (ResponseInterface $response, string $rId = null)
     * @param array $options
     * @return InsertDocument
     */
    public static function withInspector(
        string $collectionName,
        iterable $docs,
        callable $inspector,
        array $options = []
    ): InsertDocument {
        return new self($collectionName, $docs, $options, $inspector);
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
        foreach ($this->streamEvents as $streamEvent) {
            yield $this->buildAppendBatch(
                HttpHelper::METHOD_POST,
                Urls::URL_DOCUMENT . '/' . $this->collectionName,
                $streamEvent,
                $this->options
            );
        }
    }

    public function toJs(): string
    {
        if ($this->streamEvents instanceof JsonIterator) {
            return 'var rId = db.' . $this->collectionName
                . '.insert(' . $this->streamEvents->asJson() . ', ' .  json_encode($this->options) . ');';
        }

        return 'var rId = db.' . $this->collectionName
            . '.insert(' . json_encode($this->streamEvents) . ', ' .  json_encode($this->options) . ');';
    }

    public function count(): int
    {
        return count($this->streamEvents);
    }
}
