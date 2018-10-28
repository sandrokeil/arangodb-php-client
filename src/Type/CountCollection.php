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

final class CountCollection implements CollectionType
{
    /**
     * @var string
     */
    private $collectionName;

    private function __construct(string $collectionName)
    {
        $this->collectionName = $collectionName;
    }

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/Collection/Getting.html#return-number-of-documents-in-a-collection
     * @see https://docs.arangodb.com/3.3/Manual/DataModeling/Documents/DocumentMethods.html#count
     *
     * @param string $collectionName
     * @return CountCollection
     */
    public static function with(string $collectionName): CountCollection
    {
        return new self($collectionName);
    }

    public function collectionName(): string
    {
        return $this->collectionName;
    }

    public function toRequest(): RequestInterface
    {
        return new Request(
            RequestMethodInterface::METHOD_GET,
            Urls::URL_COLLECTION . '/' . $this->collectionName . '/count'
        );
    }

    public function toJs(): string
    {
        return 'var rId = db.' . $this->collectionName . '.count();';
    }
}
