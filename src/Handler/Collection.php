<?php
/**
 * Sandro Keil (https://sandro-keil.de)
 *
 * @link      http://github.com/sandrokeil/arangodb-php-client for the canonical source repository
 * @copyright Copyright (c) 2018-2020 Sandro Keil
 * @license   http://github.com/sandrokeil/arangodb-php-client/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ArangoDb\Handler;

use ArangoDb\Exception\GuardErrorException;
use ArangoDb\Exception\UnexpectedResponse;
use ArangoDb\Guard\SuccessHttpStatusCode;
use ArangoDb\SendTypeSupport;
use ArangoDb\Type\Collection as CollectionType;
use ArangoDb\Util\Json;
use Psr\Http\Message\ResponseInterface;

final class Collection implements CollectionHandler
{
    /**
     * @var SendTypeSupport
     **/
    private $client;

    /**
     * @var SuccessHttpStatusCode
     */
    private static $guard;

    public function __construct(SendTypeSupport $client)
    {
        $this->client = $client;
        self::$guard = SuccessHttpStatusCode::withoutContentId();
    }

    public function create(string $collectionName, array $options = []): string
    {
        $type = CollectionType::create($collectionName, $options)
            ->useGuard(self::$guard);

        $response = $this->client->sendType($type);

        $data = Json::decode($response->getBody()->getContents());

        if (!isset($data['id'])) {
            throw UnexpectedResponse::forType($type, $response);
        }

        return $data['id'];
    }

    public function has(string $collectionName): bool
    {
        $type = CollectionType::info($collectionName)
            ->useGuard(self::$guard);

        try {
            $this->client->sendType($type);

            return true;
        } catch (GuardErrorException $e) {
            return false;
        }
    }

    public function drop(string $collectionName): void
    {
        $type = CollectionType::delete($collectionName)
            ->useGuard(self::$guard);

        $this->client->sendType($type);
    }

    public function count(string $collectionName): int
    {
        $type = CollectionType::count($collectionName)
            ->useGuard(self::$guard);

        $response = $this->client->sendType($type);

        $data = Json::decode($response->getBody()->getContents());

        if (!isset($data['count'])) {
            throw UnexpectedResponse::forType($type, $response);
        }

        return $data['count'];
    }

    public function get(string $collectionName): ResponseInterface
    {
        $type = CollectionType::info($collectionName)
            ->useGuard(self::$guard);

        return $this->client->sendType($type);
    }

    public function truncate(string $collectionName): void
    {
        $type = CollectionType::truncate($collectionName)
            ->useGuard(self::$guard);

        $this->client->sendType($type);
    }
}
