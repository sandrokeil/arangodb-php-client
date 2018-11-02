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

final class ReadCollection implements CollectionType
{
    /**
     * @var string
     */
    private $collectionName;

    /**
     * @var array
     */
    private $options;

    private function __construct(string $collectionName, array $options)
    {
        $this->collectionName = $collectionName;
        $this->options = $options;
    }

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/Collection/Getting.html#return-information-about-a-collection
     * @see https://docs.arangodb.com/3.3/Manual/DataModeling/Collections/#collection
     *
     * @param string $collectionName
     * @param array $options
     * @return ReadCollection
     */
    public static function with(string $collectionName, array $options = []): ReadCollection
    {
        return new self($collectionName, $options);
    }

    public function collectionName(): string
    {
        return $this->collectionName;
    }

    public function toRequest(): RequestInterface
    {
        return new Request(
            RequestMethodInterface::METHOD_GET . '?' . http_build_query($this->options),
            Urls::URL_COLLECTION
        );
    }

    public function toJs(): string
    {
        return 'var rId = db._collection("' . $this->collectionName . '");';
    }
}
