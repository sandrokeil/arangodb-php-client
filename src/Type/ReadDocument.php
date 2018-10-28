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

use ArangoDBClient\Urls;
use Fig\Http\Message\RequestMethodInterface;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class ReadDocument implements Type, HasResponse
{
    use ToHttpTrait;

    /**
     * @var string
     */
    private $collectionName;

    /**
     * @var array
     */
    private $id;

    /**
     * @var string
     */
    private $result = '{}';

    /**
     * Inspects response
     *
     * @var callable
     */
    private $inspector;

    private function __construct(
        string $collectionName,
        string $id,
        callable $inspector = null
    ) {
        $this->collectionName = $collectionName;
        $this->id = $id;
        $this->inspector = $inspector ?: function (ResponseInterface $response, string $rId = null) {
            return null;
        };
    }

    /**
     * @see https://docs.arangodb.com/3.2/HTTP/Document/WorkingWithDocuments.html#read-document
     * @see https://docs.arangodb.com/3.2/Manual/DataModeling/Documents/DocumentMethods.html#document
     *
     * @param string $collectionName
     * @param string $id
     * @return ReadDocument
     */
    public static function with(string $collectionName, string $id): ReadDocument
    {
        return new self($collectionName, $id);
    }

    /**
     * @see https://docs.arangodb.com/3.2/HTTP/Document/WorkingWithDocuments.html#read-document
     * @see https://docs.arangodb.com/3.2/Manual/DataModeling/Documents/DocumentMethods.html#document
     *
     * @param string $collectionName
     * @param string $id
     * @param callable $inspector Inspects result, signature is (ResponseInterface $response, string $rId = null)
     * @return ReadDocument
     */
    public static function withInspector(
        string $collectionName,
        string $id,
        callable $inspector
    ): ReadDocument {
        return new self($collectionName, $id, $inspector);
    }

    public function collectionName(): string
    {
        return $this->collectionName;
    }

    public function checkResponse(ResponseInterface $response, string $rId = null): ?int
    {
        $this->result = $response->getBody();

        return ($this->inspector)($response, $rId);
    }

    public function toRequest(): RequestInterface
    {
        return new Request(
            RequestMethodInterface::METHOD_GET,
            Urls::URL_DOCUMENT . '/' . $this->collectionName . '/' . $this->id
        );
    }

    public function toJs(): string
    {
        return 'var rId = db.' . $this->collectionName . '.document(' . $this->id . ');';
    }

    public function rawResult(): ?string
    {
        return $this->result === '{}' ? null : $this->result;
    }

    public function result()
    {
        return json_decode($this->result, true) ?? null;
    }
}
