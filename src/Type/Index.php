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

use ArangoDb\Guard\Guard;
use ArangoDb\Http\VpackStream;
use ArangoDb\Url;
use Fig\Http\Message\RequestMethodInterface;
use ArangoDb\Http\Request;
use Psr\Http\Message\RequestInterface;

final class Index implements IndexType, GuardSupport
{
    /**
     * @var string|null
     */
    private $name;

    /**
     * @var string|null
     */
    private $collectionName;

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
        ?string $collectionName,
        string $uri = '',
        string $method = RequestMethodInterface::METHOD_GET,
        array $options = []
    ) {
        $this->name = $name;
        $this->collectionName = $collectionName;
        $this->uri = $uri;
        $this->method = $method;
        $this->options = $options;
    }

    public static function listAll(string $collectionName): IndexType
    {
        return new self(null, $collectionName, '?' . http_build_query(['collection' => $collectionName]));
    }

    public static function info(string $indexName): IndexType
    {
        return new self($indexName, null, '/' . $indexName);
    }

    public static function create(string $collectionName, array $options = []): IndexType
    {
        return new self(
            null,
            $collectionName,
            '?' . http_build_query(['collection' => $collectionName]),
            RequestMethodInterface::METHOD_POST,
            $options
        );
    }

    public static function delete(string $indexName): IndexType
    {
        return new self(
            $indexName,
            null,
            '/' . $indexName,
            RequestMethodInterface::METHOD_DELETE
        );
    }

    public function toRequest(): RequestInterface
    {
        if (! empty($this->options)) {
            return new Request(
                $this->method,
                Url::INDEX . $this->uri,
                [],
                new VpackStream($this->options)
            );
        }

        return new Request(
            $this->method,
            Url::INDEX . $this->uri
        );
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
