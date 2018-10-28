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

final class QueryFirstByExample implements CollectionType
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
    private $options;

    private function __construct(string $collectionName, array $example, array $options = [])
    {
        $this->collectionName = $collectionName;
        $this->example = $example;
        $this->options = $options;
    }

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/SimpleQuery/#find-documents-matching-an-example
     * @see https://docs.arangodb.com/3.3/Manual/DataModeling/Documents/DocumentMethods.html#first-example
     *
     * @param string $collectionName
     * @param array $example
     * @param array $options
     * @return QueryFirstByExample
     */
    public static function with(string $collectionName, array $example, array $options = []): QueryFirstByExample
    {
        return new self($collectionName, $example, $options);
    }

    public function collectionName(): string
    {
        return $this->collectionName;
    }

    public function toRequest(): RequestInterface
    {
        return new Request(
            RequestMethodInterface::METHOD_PUT,
            Urls::URL_EXAMPLE,
            [],
            new VpackStream(
                array_merge(
                    $this->options,
                    [
                        'collection' => $this->collectionName,
                        'example' => $this->example,
                    ]
                )
            )
        );
    }

    public function toJs(): string
    {
        $args = '';

        foreach ($this->example as $field => $value) {
            $args .= $field . ', ' . $value;
        }

        return 'var rId = db.' . $this->collectionName . '.firstExample(' . $args . ');';
    }
}
