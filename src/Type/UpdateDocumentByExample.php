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

use ArangoDb\Exception\LogicException;
use ArangoDBClient\HttpHelper;
use ArangoDBClient\Urls;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class UpdateDocumentByExample implements Type
{
    /**
     * @var string
     */
    private $collectionName;

    /**
     * @var array
     */
    private $example;

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
        array $example,
        array $data,
        array $options = [],
        callable $inspector = null
    ) {
        $this->collectionName = $collectionName;
        $this->data = $data;
        $this->example = $example;
        $this->options = $options;
        $this->inspector = $inspector ?: function (ResponseInterface $response, string $rId = null) {
            return strpos($response->getBody(), '"' . $rId . '"' . ':0') !== false ? 404 : null;
        };
    }

    /**
     * @see https://docs.arangodb.com/3.2/HTTP/Document/WorkingWithDocuments.html#update-documents
     * @see https://docs.arangodb.com/3.2/Manual/DataModeling/Documents/DocumentMethods.html#update-by-example
     *
     * @param string $collectionName
     * @param array $example
     * @param array $data
     * @param array $options
     * @return UpdateDocumentByExample
     */
    public static function with(
        string $collectionName,
        array $example,
        array $data,
        array $options = []
    ): UpdateDocumentByExample {
        return new self($collectionName, $example, $data, $options);
    }

    /**
     * @see https://docs.arangodb.com/3.2/HTTP/Document/WorkingWithDocuments.html#update-documents
     * @see https://docs.arangodb.com/3.2/Manual/DataModeling/Documents/DocumentMethods.html#update-by-example
     *
     * @param string $collectionName
     * @param array $example
     * @param callable $inspector Inspects result, signature is (ResponseInterface $response, string $rId = null)
     * @param array $options
     * @return UpdateDocumentByExample
     */
    public static function withInspector(
        string $collectionName,
        array $example,
        callable $inspector,
        array $options = []
    ): UpdateDocumentByExample {
        return new self($collectionName, $example, $options, $inspector);
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
        throw new LogicException('Not possible at the moment, see ArangoDB docs');
    }

    public function toJs(): string
    {
        return 'var rId = db.' . $this->collectionName
            . '.updateByExample('
            . json_encode($this->example) . ', '
            . json_encode($this->data) . ', '
            . json_encode($this->options)
            . ');';
    }
}
