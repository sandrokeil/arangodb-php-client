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

use ArangoDb\Http\VpackStream;
use ArangoDb\Url;
use Fig\Http\Message\RequestMethodInterface;
use ArangoDb\Http\Request;
use Psr\Http\Message\RequestInterface;

final class Database implements DatabaseType
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

    public static function create(string $databaseName, array $options = []): DatabaseType
    {
        $options['name'] = $databaseName;
        return new self($databaseName, '', RequestMethodInterface::METHOD_POST, $options);
    }

    public static function delete(string $databaseName): DatabaseType
    {
        return new self($databaseName, '/' . $databaseName, RequestMethodInterface::METHOD_DELETE);
    }

    public static function info(): DatabaseType
    {
        return new self('', '/current');
    }

    public static function listAccessible(): DatabaseType
    {
        return new self('', '/user');
    }

    public static function listAll(): DatabaseType
    {
        return new self('');
    }

    public function toRequest(): RequestInterface
    {
        if (empty($this->options)) {
            return new Request(
                $this->method,
                Url::DATABASE . $this->uri
            );
        }

        return new Request(
            $this->method,
            Url::DATABASE . $this->uri,
            [],
            new VpackStream($this->options)
        );
    }
}
