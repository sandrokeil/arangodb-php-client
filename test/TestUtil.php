<?php
/**
 * Sandro Keil (https://sandro-keil.de)
 *
 * @link      http://github.com/sandrokeil/arangodb-php-client for the canonical source repository
 * @copyright Copyright (c) 2018 Sandro Keil
 * @license   http://github.com/sandrokeil/arangodb-php-client/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ArangoDbTest;

use ArangoDb\Client;
use ArangoDb\Exception\ArangoDbException;
use ArangoDb\Http\Request;
use ArangoDb\Http\VpackStream;
use ArangoDb\Type\Database;
use ArangoDb\ClientOptions;
use ArangoDb\Url;
use Fig\Http\Message\RequestMethodInterface;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;

final class TestUtil
{
    public static function getClient(): ClientInterface
    {
        $type = 'application/' . (getenv('USE_VPACK') === 'true' ? 'x-velocypack' : 'json');
        $params = self::getConnectionParams();

        return new Client(
            $params,
            [
                'Content-Type' => [$type],
                'Accept' => [$type],
            ]
        );
    }

    public static function createDatabase(): void
    {
        $type = 'application/' . (getenv('USE_VPACK') === 'true' ? 'x-velocypack' : 'json');
        $params = self::getConnectionParams();

        if ($params[ClientOptions::OPTION_DATABASE] === '_system') {
            throw new \RuntimeException('"_system" database can not be created. Choose another database for tests.');
        }

        $params[ClientOptions::OPTION_DATABASE] = '_system';

        $client = new Client(
            $params,
            [
                'Content-Type' => [$type],
                'Accept' => [$type],
            ]
        );
        $response = $client->sendRequest(Database::create(self::getDatabaseName())->toRequest());

        if ($response->getStatusCode() !== StatusCodeInterface::STATUS_CREATED) {
            self::dropDatabase();
            throw new \RuntimeException($response->getBody()->getContents());
        }
    }

    public static function dropDatabase(): void
    {
        $type = 'application/' . (getenv('USE_VPACK') === 'true' ? 'x-velocypack' : 'json');
        $params = self::getConnectionParams();

        if ($params[ClientOptions::OPTION_DATABASE] === '_system') {
            throw new \RuntimeException('"_system" database can not be dropped. Choose another database for tests.');
        }

        $params[ClientOptions::OPTION_DATABASE] = '_system';

        $client = new Client(
            $params,
            [
                'Content-Type' => [$type],
                'Accept' => [$type],
            ]
        );
        $client->sendRequest(Database::delete(self::getDatabaseName())->toRequest());
    }

    public static function getResponseContent(ResponseInterface $response): string
    {
        $body = $response->getBody();

        if ($body instanceof VpackStream) {
            $content = $body->vpack()->toJson();
        } else {
            $content = $body->getContents();
        }
        return $content;
    }

    public static function getDatabaseName(): string
    {
        if (!self::hasRequiredConnectionParams()) {
            throw new \RuntimeException('No connection params given');
        }

        return getenv('arangodb_dbname');
    }

    public static function getConnectionParams(): array
    {
        if (!self::hasRequiredConnectionParams()) {
            throw new \RuntimeException('No connection params given');
        }

        return self::getSpecifiedConnectionParams();
    }

    public static function deleteCollection(ClientInterface $client, string $collection): void
    {
        try {
            $client->sendRequest(
                new Request(
                    RequestMethodInterface::METHOD_DELETE,
                    Url::COLLECTION . '/' . $collection
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
