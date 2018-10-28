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

final class DeleteDocumentByExample implements Type
{
    use ToHttpTrait;

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
        array $options = [],
        callable $inspector = null
    ) {

        $this->collectionName = $collectionName;
        $this->example = $example;
        $this->options = $options;
        $this->inspector = $inspector ?: function (ResponseInterface $response, string $rId = null) {
            if ($rId) {
                return null;
            }

            return strpos($response->getBody(), '"deleted":0') !== false ? 404 : null;
        };
    }

    /**
     * @see https://docs.arangodb.com/3.2/HTTP/Document/WorkingWithDocuments.html#removes-multiple-documents
     * @see https://docs.arangodb.com/3.2/Manual/DataModeling/Documents/DocumentMethods.html#remove-by-example
     *
     * @param string $collectionName
     * @param array $example
     * @param array $options
     * @return DeleteDocumentByExample
     */
    public static function with(string $collectionName, array $example, array $options = []): DeleteDocumentByExample
    {
        return new self($collectionName, $example, $options);
    }

    /**
     * @see https://docs.arangodb.com/3.2/HTTP/Document/WorkingWithDocuments.html#removes-multiple-documents
     * @see https://docs.arangodb.com/3.2/Manual/DataModeling/Documents/DocumentMethods.html#remove-by-example
     *
     * @param string $collectionName
     * @param array $example
     * @param callable $inspector Inspects result, signature is (ResponseInterface $response, string $rId = null)
     * @param array $options
     * @return DeleteDocumentByExample
     */
    public static function withInspector(
        string $collectionName,
        array $example,
        callable $inspector,
        array $options = []
    ): DeleteDocumentByExample {
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
        return new Request(
            RequestMethodInterface::METHOD_PUT,
            Urls::URL_REMOVE_BY_EXAMPLE . '/?' . http_build_query($this->options),
            [],
            new VpackStream(
                [
                    'collection' => $this->collectionName,
                    'example' => $this->example,
                ]
            )
        );
    }

    public function toJs(): string
    {
        $options = ! empty($this->options['waitForSync']) ? ', true' : ', false';

        if (! empty($this->options['limit'])) {
            $options .= ', ' . (int) $this->options['limit'];
        }

        return 'var rId = db.' . $this->collectionName . '.removeByExample('
            . json_encode($this->example) . $options
            . ');';
    }
}
