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

class CreateIndex implements Type
{
    use ToHttpTrait;

    /**
     * @var string
     */
    private $collectionName;

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
        array $options,
        callable $inspector = null
    ) {
        $this->collectionName = $collectionName;
        $this->options = $options;
        $this->inspector = $inspector ?: function (ResponseInterface $response, string $rId = null) {
            if ($rId) {
                return null;
            }

            return strpos($response->getBody(), '"error":false') === false ? 422 : null;
        };
    }

    /**
     * @see https://docs.arangodb.com/3.2/HTTP/Indexes/WorkingWith.html#create-index
     * @see https://docs.arangodb.com/3.2/Manual/Indexing/WorkingWithIndexes.html#creating-an-index
     *
     * @param string $collectionName
     * @param array $options
     * @return CreateIndex
     */
    public static function with(string $collectionName, array $options = []): CreateIndex
    {
        return new self($collectionName, $options);
    }

    /**
     * @see https://docs.arangodb.com/3.2/HTTP/Indexes/WorkingWith.html#create-index
     * @see https://docs.arangodb.com/3.2/Manual/Indexing/WorkingWithIndexes.html#creating-an-index
     *
     * @param string $collectionName
     * @param callable $inspector Inspects result, signature is (ResponseInterface $response, string $rId = null)
     * @param array $options
     * @return CreateIndex
     */
    public static function withInspector(
        string $collectionName,
        callable $inspector,
        array $options = []
    ): CreateIndex {
        return new self($collectionName, $options, $inspector);
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
            RequestMethodInterface::METHOD_POST,
            Urls::URL_INDEX . '/?' . http_build_query(['collection' => $this->collectionName]),
            [],
            new VpackStream($this->options)
        );
    }

    public function toJs(): string
    {
        return 'var rId = db.' . $this->collectionName . '.ensureIndex(' . json_encode($this->options) . ');';
    }
}
