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
use ArangoDb\Statement\VpackStreamHandler;
use ArangoDb\Statement\VpackStreamHandlerFactory;
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
use Psr\Http\Message\StreamInterface;
use Zend\Diactoros\Request;
use Zend\Diactoros\Response;
use Zend\Diactoros\Stream;
use Velocypack\Vpack;
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
                $type = 'application/' . (getenv('USE_VPACK') === 'true' ? 'x-velocypack' : 'json');

                $request = new Request($uri, $method);
                $request = $request->withAddedHeader('Content-Type', $type);
                return $request->withAddedHeader('Accept', $type);
            }
        };
    }

    public static function getStreamFactory(bool $forceJson = false): StreamFactoryInterface
    {
        if (true === $forceJson || getenv('USE_VPACK') !== 'true') {
            return new StreamFactory();
        }

        return new class implements StreamFactoryInterface
        {
            public function createStream(string $content = '') : StreamInterface
            {
                $vpack = strpos($content, '{') === 0 || strpos($content, '[') === 0 ? Vpack::fromJson($content) : Vpack::fromBinary($content);
                $resource = fopen('php://temp', 'r+');
                fwrite($resource, $vpack->toBinary());
                rewind($resource);

                return $this->createStreamFromResource($resource);
            }

            public function createStreamFromFile(string $file, string $mode = 'r') : StreamInterface
            {
                return new Stream($file, $mode);
            }

            public function createStreamFromResource($resource) : StreamInterface
            {
                if (! is_resource($resource) || 'stream' !== get_resource_type($resource)) {
                    throw new \InvalidArgumentException(
                        'Invalid stream provided; must be a stream resource'
                    );
                }
                return new Stream($resource);
            }
        };
    }

    public static function getStreamHandlerFactory(): StreamHandlerFactoryInterface
    {
        return getenv('USE_VPACK') === 'true'
            ? new VpackStreamHandlerFactory(VpackStreamHandler::RESULT_TYPE_ARRAY)
            : new ArrayStreamHandlerFactory();
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

    public static function getResponseContent(ResponseInterface $response, bool $forceJson = false): array
    {
        $body = $response->getBody();
        $body->rewind();
        return getenv('USE_VPACK') === 'true' && false === $forceJson
            ? Vpack::fromBinary($body->getContents())->toArray()
            : json_decode($body->getContents(), true);
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
