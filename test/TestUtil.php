<?php
/**
 * Sandro Keil (https://sandro-keil.de)
 *
 * @link      http://github.com/sandrokeil/arangodb-php-client for the canonical source repository
 * @copyright Copyright (c) 2018-2019 Sandro Keil
 * @license   http://github.com/sandrokeil/arangodb-php-client/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ArangoDbTest;

use ArangoDb\Client;
use ArangoDb\Exception\ArangoDbException;
use ArangoDb\Statement\ArrayStreamHandlerFactory;
use ArangoDb\Statement\StreamHandlerFactoryInterface;
use ArangoDb\Type\Database;
use ArangoDb\ClientOptions;
use ArangoDb\Url;
use Fig\Http\Message\RequestMethodInterface;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Zend\Diactoros\Request;
use Zend\Diactoros\Response;
use Zend\Diactoros\StreamFactory;

final class TestUtil
{
    public static function getClient(): ClientInterface
    {
        $params = self::getConnectionParams();

        return new Client(
            $params,
            self::getResponseFactory(),
            self::getStreamFactory()
        );
    }

    public static function getResponseFactory(): ResponseFactoryInterface
    {
        return new class implements ResponseFactoryInterface
        {
            public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
            {
                $response = new Response();

                if ($reasonPhrase !== '') {
                    return $response->withStatus($code, $reasonPhrase);
                }

                return $response->withStatus($code);
            }
        };
    }

    public static function getRequestFactory(): RequestFactoryInterface
    {
        return new class implements RequestFactoryInterface
        {
            public function createRequest(string $method, $uri): RequestInterface
            {
                return new Request($uri, $method);
            }
        };
    }

    public static function getStreamFactory(): StreamFactoryInterface
    {
        return new StreamFactory();
    }

    public static function getStreamHandlerFactory(): StreamHandlerFactoryInterface
    {
        return new ArrayStreamHandlerFactory();
    }

    public static function createDatabase(): void
    {
        $params = self::getConnectionParams();

        if ($params[ClientOptions::OPTION_DATABASE] === '_system') {
            throw new \RuntimeException('"_system" database can not be created. Choose another database for tests.');
        }

        $params[ClientOptions::OPTION_DATABASE] = '_system';

        $client = new Client($params, self::getResponseFactory(), self::getStreamFactory());
        $response = $client->sendRequest(
            Database::create(self::getDatabaseName())->toRequest(self::getRequestFactory(), self::getStreamFactory())
        );

        if ($response->getStatusCode() !== StatusCodeInterface::STATUS_CREATED) {
            self::dropDatabase();
            throw new \RuntimeException($response->getBody()->getContents());
        }
    }

    public static function dropDatabase(): void
    {
        $params = self::getConnectionParams();

        if ($params[ClientOptions::OPTION_DATABASE] === '_system') {
            throw new \RuntimeException('"_system" database can not be dropped. Choose another database for tests.');
        }

        $params[ClientOptions::OPTION_DATABASE] = '_system';

        $client = new Client(
            $params,
            self::getResponseFactory(),
            self::getStreamFactory()
        );
        $client->sendRequest(
            Database::delete(self::getDatabaseName())->toRequest(self::getRequestFactory(), self::getStreamFactory())
        );
    }

    public static function getResponseContent(ResponseInterface $response): string
    {
        return $response->getBody()->getContents();
    }

    public static function getDatabaseName(): string
    {
        if (! self::hasRequiredConnectionParams()) {
            throw new \RuntimeException('No connection params given');
        }

        return getenv('arangodb_dbname');
    }

    public static function getConnectionParams(): array
    {
        if (! self::hasRequiredConnectionParams()) {
            throw new \RuntimeException('No connection params given');
        }

        return self::getSpecifiedConnectionParams();
    }

    public static function deleteCollection(ClientInterface $client, string $collection): void
    {
        try {
            $client->sendRequest(
                new Request(
                    Url::COLLECTION . '/' . $collection,
                    RequestMethodInterface::METHOD_DELETE
                )
            );
        } catch (ArangoDbException $e) {
            // needed if test deletes collection
        }
    }

    private static function hasRequiredConnectionParams(): bool
    {
        $env = getenv();

        return isset(
            $env['arangodb_host'],
            $env['arangodb_dbname']
        );
    }

    private static function getSpecifiedConnectionParams(): array
    {
        return [
            ClientOptions::OPTION_ENDPOINT => getenv('arangodb_host'),
            ClientOptions::OPTION_DATABASE => getenv('arangodb_dbname'),
        ];
    }
}
