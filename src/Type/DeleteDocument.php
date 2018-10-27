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
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class DeleteDocument implements Type
{
    use ToHttpTrait;

    /**
     * @var string
     */
    private $collectionName;

    /**
     * @var array
     */
    private $keys;

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
        array $keys,
        array $options = [],
        callable $inspector = null
    ) {

        $this->collectionName = $collectionName;
        $this->keys = $keys;
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
     * @see https://docs.arangodb.com/3.2/HTTP/Document/WorkingWithDocuments.html#removes-multiple-documents
     * @see https://docs.arangodb.com/3.2/Manual/DataModeling/Documents/DocumentMethods.html#remove-by-example
     *
     * @param string $collectionName
     * @param array $keys
     * @param array $options
     * @return DeleteDocument
     */
    public static function with(string $collectionName, array $keys, array $options = []): DeleteDocument
    {
        return new self($collectionName, $keys, $options);
    }

    /**
     * @see https://docs.arangodb.com/3.2/HTTP/Document/WorkingWithDocuments.html#removes-multiple-documents
     * @see https://docs.arangodb.com/3.2/Manual/DataModeling/Documents/DocumentMethods.html#remove-by-example
     *
     * @param string $collectionName
     * @param array $keys
     * @param callable $inspector Inspects result, signature is (ResponseInterface $response, string $rId = null)
     * @param array $options
     * @return DeleteDocument
     */
    public static function withInspector(
        string $collectionName,
        array $keys,
        callable $inspector,
        array $options = []
    ): DeleteDocument {
        return new self($collectionName, $keys, $options, $inspector);
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
        return $this->buildAppendBatch(
            HttpHelper::METHOD_DELETE,
            Urls::URL_DOCUMENT . '/' . $this->collectionName,
            $this->keys,
            $this->options
        );
    }

    public function toJs(): string
    {
        $options = ! empty($this->options['waitForSync']) ? ', true' : ', false';

        if (! empty($this->options['limit'])) {
            $options .= ', ' . (int) $this->options['limit'];
        }

        return 'var rId = db.' . $this->collectionName . '.removeByKeys(' . json_encode($this->keys) . $options . ');';
    }
}
