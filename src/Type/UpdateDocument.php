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
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class UpdateDocument implements Type
{
    use ToHttpTrait;

    /**
     * @var string
     */
    private $collectionName;

    /**
     * @var string
     */
    private $id;

    /**
     * @var array
     */
    private $data;

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
        string $id,
        array $data,
        array $options = [],
        callable $inspector = null
    ) {
        $this->collectionName = $collectionName;
        $this->data = $data;
        $this->id = $id;
        $this->options = $options;
        $this->inspector = $inspector ?: function (ResponseInterface $response, string $rId = null) {
            if ($rId) {
                return null;
            }

            return strpos($response->getBody(), '"error":false') === false
            && strpos($response->getBody(), '"_key":"') === false ? 422 : null;
        };
    }

    /**
     * @see https://docs.arangodb.com/3.2/HTTP/Document/WorkingWithDocuments.html#update-document
     * @see https://docs.arangodb.com/3.2/Manual/DataModeling/Documents/DocumentMethods.html#update
     *
     * @param string $collectionName
     * @param string $id
     * @param array $data
     * @param array $options
     * @return UpdateDocument
     */
    public static function with(string $collectionName, string $id, array $data, array $options = []): UpdateDocument
    {
        return new self($collectionName, $id, $data, $options);
    }

    /**
     * @see https://docs.arangodb.com/3.2/HTTP/Document/WorkingWithDocuments.html#update-document
     * @see https://docs.arangodb.com/3.2/Manual/DataModeling/Documents/DocumentMethods.html#update
     *
     * @param string $collectionName
     * @param string $id
     * @param callable $inspector Inspects result, signature is (ResponseInterface $response, string $rId = null)
     * @param array $options
     * @return UpdateDocument
     */
    public static function withInspector(
        string $collectionName,
        string $id,
        callable $inspector,
        array $options = []
    ): UpdateDocument {
        return new self($collectionName, $id, $options, $inspector);
    }

    public function checkResponse(ResponseInterface $response, string $rId = null): ?int
    {
        return ($this->inspector)($response, $rId);
    }

    public function collectionName(): string
    {
        return $this->collectionName;
    }

    public function toRequest(): RequestInterface
    {
        return new Request(
            RequestMethodInterface::METHOD_PATCH,
            Urls::URL_DOCUMENT . '/' . $this->collectionName . '/?' . http_build_query($this->options),
            [],
            new VpackStream($this->data)
        );
    }

    public function toJs(): string
    {
        return 'var rId = db.' . $this->collectionName
            . '.update("' . $this->id . '", ' . json_encode($this->data) . ', ' . json_encode($this->options) . ');';
    }
}
