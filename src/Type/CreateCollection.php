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
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class CreateCollection implements Type
{
    /**
     * @var string
     */
    private $name;

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
        string $name,
        array $options = [],
        callable $inspector = null
    ) {
        $this->name = $name;
        $this->options = $options;
        $this->inspector = $inspector ?: function (ResponseInterface $response, string $rId = null) {
            if ($rId) {
                return null;
            }

            return strpos($response->getBody(), '"error":false') === false ? 422 : null;
        };
    }

    /**
     * @see https://docs.arangodb.com/3.2/HTTP/Collection/Creating.html#create-collection
     * @see https://docs.arangodb.com/3.2/Manual/DataModeling/Collections/DatabaseMethods.html#create
     *
     * @param string $collectionName
     * @param array $options
     * @return CreateCollection
     */
    public static function with(string $collectionName, array $options = []): CreateCollection
    {
        return new self($collectionName, $options);
    }

    /**
     * @see https://docs.arangodb.com/3.2/HTTP/Collection/Creating.html#create-collection
     * @see https://docs.arangodb.com/3.2/Manual/DataModeling/Collections/DatabaseMethods.html#create
     *
     * @param string $collectionName
     * @param callable $inspector Inspects result, signature is (Response $response, string $rId = null)
     * @param array $options
     * @return CreateCollection
     */
    public static function withInspector(
        string $collectionName,
        callable $inspector,
        array $options = []
    ): CreateCollection {
        return new self($collectionName, $options, $inspector);
    }

    public function checkResponse(ResponseInterface $response, string $rId = null): ?int
    {
        return ($this->inspector)($response, $rId);
    }

    public function collectionName(): string
    {
        return $this->name;
    }

    public function toRequest(): RequestInterface
    {
        $options = $this->options;
        $options['name'] = $this->name;

        return new Request(
            HttpHelper::METHOD_POST,
            Urls::URL_COLLECTION,
            [],
            json_encode($options) // TODO how to handle vpack, __toString useful ?
        );
    }

    public function toJs(): string
    {
        return 'var rId = db._create("' . $this->name . '", ' . json_encode($this->options) . ');';
    }
}
