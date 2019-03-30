<?php
/**
 * Sandro Keil (https://sandro-keil.de)
 *
 * @link      http://github.com/sandrokeil/arangodb-php-client for the canonical source repository
 * @copyright Copyright (c) 2018-2019 Sandro Keil
 * @license   http://github.com/sandrokeil/arangodb-php-client/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ArangoDb\Type;

use ArangoDb\Guard\Guard;
use ArangoDb\Url;
use ArangoDb\Util\Json;
use Fig\Http\Message\RequestMethodInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

final class Collection implements CollectionType
{
    /**
     * @var string|null
     */
    private $name;

    /**
     * @var array
     */
    private $options;

    /**
     * @var string
     */
    private $uri;

    /**
     * @var string
     */
    private $method;

    /**
     * Guard
     *
     * @var Guard
     */
    private $guard;

    private function __construct(
        ?string $name,
        string $uri = '',
        string $method = RequestMethodInterface::METHOD_GET,
        array $options = []
    ) {
        $this->name = $name;
        $this->uri = $uri;
        $this->method = $method;
        $this->options = $options;
    }

    public static function create(string $collectionName, array $options = []): CollectionType
    {
        $options['name'] = $collectionName;
        return new self($collectionName, '', RequestMethodInterface::METHOD_POST, $options);
    }

    public static function listAll(bool $excludeSystem = true): CollectionType
    {
        return new self(null, '?excludeSystem=' . ($excludeSystem ? 'true' : 'false'));
    }

    public static function info(string $collectionName): CollectionType
    {
        return new self($collectionName);
    }

    public static function checksum(string $collectionName): CollectionType
    {
        return new self($collectionName, '/checksum');
    }

    public static function count(string $collectionName): CollectionType
    {
        return new self($collectionName, '/count');
    }

    public static function figures(string $collectionName): CollectionType
    {
        return new self($collectionName, '/figures');
    }

    public static function properties(string $collectionName): CollectionType
    {
        return new self($collectionName, '/properties');
    }

    public static function revision(string $collectionName): CollectionType
    {
        return new self($collectionName, '/revision');
    }

    public static function delete(string $collectionName): CollectionType
    {
        return new self($collectionName, '', RequestMethodInterface::METHOD_DELETE);
    }

    public static function load(string $collectionName): CollectionType
    {
        return new self($collectionName, '/load', RequestMethodInterface::METHOD_PUT);
    }

    public static function loadIndexes(string $collectionName): CollectionType
    {
        return new self($collectionName, '/loadIndexesIntoMemory', RequestMethodInterface::METHOD_PUT);
    }

    public static function updateProperties(
        string $collectionName,
        bool $waitForSync = null,
        int $journalSize = null
    ): CollectionType {
        $options = [];

        if ($waitForSync !== null) {
            $options['waitForSync'] = $waitForSync;
        }
        if ($journalSize !== null) {
            $options['journalSize'] = $journalSize;
        }

        return new self($collectionName, '/properties', RequestMethodInterface::METHOD_PUT, $options);
    }

    public static function rename(string $collectionName, string $newCollectionName): CollectionType
    {
        return new self($collectionName, '/rename', RequestMethodInterface::METHOD_PUT, ['name' => $newCollectionName]);
    }

    public static function rotate(string $collectionName): CollectionType
    {
        return new self($collectionName, '/rotate', RequestMethodInterface::METHOD_PUT);
    }

    public static function truncate(string $collectionName): CollectionType
    {
        return new self($collectionName, '/truncate', RequestMethodInterface::METHOD_PUT);
    }

    public static function unload(string $collectionName): CollectionType
    {
        return new self($collectionName, '/unload', RequestMethodInterface::METHOD_PUT);
    }

    public function toRequest(
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory
    ): RequestInterface {
        $uri = $this->uri;

        if ($this->name !== '' && $this->method !== RequestMethodInterface::METHOD_POST) {
            $uri = '/' . $this->name . $uri;
        }

        $request = $requestFactory->createRequest($this->method, Url::COLLECTION . $uri);

        if (0 === count($this->options)) {
            return $request;
        }
        return $request->withBody($streamFactory->createStream(Json::encode($this->options)));
    }

    public function useGuard(Guard $guard): Type
    {
        $this->guard = $guard;
        return $this;
    }

    public function guard(): ?Guard
    {
        return $this->guard;
    }
}
