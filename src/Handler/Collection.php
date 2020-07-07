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
use ArangoDb\Guard\Guard;
use ArangoDb\Guard\SuccessHttpStatusCode;
use ArangoDb\Http\TypeSupport;
use ArangoDb\Type\Collection as CollectionType;
use ArangoDb\Util\Json;
use Psr\Http\Message\ResponseInterface;

final class Collection implements CollectionHandler
{
    /**
     * @var TypeSupport
     **/
    private $client;

    /**
     * @var Guard
     */
    private $guard;

    /**
     * @var string
     */
    protected $collectionClass;

    /**
     * @param TypeSupport $client
     * @param string $collectionClass FQCN of the class which implements \ArangoDb\Type\CollectionType
     * @param Guard|null $guard
     */
    public function __construct(
        TypeSupport $client,
        string $collectionClass = CollectionType::class,
        Guard $guard = null
    ) {
        $this->client = $client;
        $this->collectionClass = $collectionClass;
        $this->guard = $guard ?? SuccessHttpStatusCode::withoutContentId();
    }

    public function create(string $collectionName, array $options = []): string
    {
        $type = ($this->collectionClass)::create($collectionName, $options)
            ->useGuard($this->guard);

        $response = $this->client->sendType($type);

        $data = Json::decode($response->getBody()->getContents());

        if (! isset($data['id'])) {
            throw UnexpectedResponse::forType($type, $response);
        }

        return $data['id'];
    }

    public function has(string $collectionName): bool
    {
        $type = ($this->collectionClass)::info($collectionName)
            ->useGuard($this->guard);

        try {
            $this->client->sendType($type);

            return true;
        } catch (GuardErrorException $e) {
            return false;
        }
    }

    public function drop(string $collectionName): void
    {
        $type = ($this->collectionClass)::delete($collectionName)
            ->useGuard($this->guard);

        $this->client->sendType($type);
    }

    public function count(string $collectionName): int
    {
        $type = ($this->collectionClass)::count($collectionName)
            ->useGuard($this->guard);

        $response = $this->client->sendType($type);

        $data = Json::decode($response->getBody()->getContents());

        if (! isset($data['count'])) {
            throw UnexpectedResponse::forType($type, $response);
        }

        return $data['count'];
    }

    public function get(string $collectionName): ResponseInterface
    {
        $type = ($this->collectionClass)::info($collectionName)
            ->useGuard($this->guard);

        return $this->client->sendType($type);
    }

    public function truncate(string $collectionName): void
    {
        $type = ($this->collectionClass)::truncate($collectionName)
            ->useGuard($this->guard);

        $this->client->sendType($type);
    }
}
