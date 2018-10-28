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

final class DeleteCollection implements CollectionType
{
    /**
     * @var string
     */
    private $collectionName;

    /**
     * @var array
     */
    private $options;

    private function __construct(string $collectionName, array $options = [])
    {
        $this->collectionName = $collectionName;
        $this->options = $options;
    }

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/Collection/Creating.html#drops-a-collection
     * @see https://docs.arangodb.com/3.3/Manual/DataModeling/Collections/CollectionMethods.html#drop
     *
     * @param string $collectionName
     * @param array $options
     * @return DeleteCollection
     */
    public static function with(string $collectionName, array $options = []): DeleteCollection
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
            RequestMethodInterface::METHOD_DELETE,
            Urls::URL_COLLECTION . '/' . $this->collectionName,
            [],
            new VpackStream($this->options)
        );
    }

    public function toJs(): string
    {
        return 'var rId = db._drop("' . $this->collectionName . '", '
            . ($this->options ? json_encode($this->options) : '{}') . ');';
    }
}
