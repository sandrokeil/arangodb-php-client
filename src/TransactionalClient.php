<?php
/**
 * Sandro Keil (https://sandro-keil.de)
 *
 * @link      http://github.com/sandrokeil/arangodb-php-client for the canonical source repository
 * @copyright Copyright (c) 2018-2019 Sandro Keil
 * @license   http://github.com/sandrokeil/arangodb-php-client/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ArangoDb;

use ArangoDb\Type\Batch;
use ArangoDb\Type\GuardSupport;
use ArangoDb\Type\Transaction as TransactionType;
use ArangoDb\Type\Transactional;
use ArangoDb\Type\Type;
use Fig\Http\Message\StatusCodeInterface;
use function MongoDB\BSON\toPHP;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;

final class TransactionalClient implements ClientInterface
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var RequestFactoryInterface
     */
    private $requestFactory;

    /**
     * @var ResponseFactoryInterface
     */
    private $responseFactory;

    /**
     * Types
     *
     * @var Type[]
     */
    private $types = [];

    /**
     * Types
     *
     * @var Transactional[]
     */
    private $transactionalTypes = [];

    /**
     * @var StreamFactoryInterface
     */
    private $streamFactory;

    public function __construct(
        ClientInterface $client,
        RequestFactoryInterface $requestFactory,
        ResponseFactoryInterface $responseFactory,
        StreamFactoryInterface $streamFactory
    ) {
        $this->client = $client;
        $this->requestFactory = $requestFactory;
        $this->responseFactory = $responseFactory;
        $this->streamFactory = $streamFactory;
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        return $this->client->sendRequest($request);
    }

    /**
     * Sends types and transactional types. Type responses and transaction response are validated via guards if provided
     * to a type. You can also manually validate the transaction response but not the non transaction response.
     *
     * @param array $params
     * @param bool $waitForSync
     * @return ResponseInterface
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function send(array $params = [], bool $waitForSync = false): ResponseInterface
    {
        if (0 !== count($this->types)) {
            $batch = Batch::fromTypes(...$this->types);
            $responseBatch = $this->client->sendRequest($batch->toRequest($this->requestFactory, $this->streamFactory));

            if (null !== ($guards = $batch->guards())) {
                BatchResult::fromResponse(
                    $responseBatch,
                    $this->responseFactory,
                    $this->streamFactory
                )->validate(...$guards);
            }
        }

        $actions = '';
        $collectionsWrite = [[]];
        $collectionsRead = [[]];
        $return = [];
        $guards = [];

        if (0 === count($this->transactionalTypes)) {
            return $this->responseFactory->createResponse(StatusCodeInterface::STATUS_OK);
        }

        foreach ($this->transactionalTypes as $key => $type) {
            $collectionsWrite[] = $type->collectionsWrite();
            $collectionsRead[] = $type->collectionsRead();

            if ($type instanceof GuardSupport
                && ($guard = $type->guard()) !== null
            ) {
                $guards[] = $guard;
                $key = $guard->contentId();
            }
            $actions .= str_replace('var rId', 'var rId' . $key, $type->toJs());
            $return[] = 'rId' . $key;
        }
        $collectionsWrite = array_merge(...$collectionsWrite);
        $collectionsRead = array_merge(...$collectionsRead);

        $response = $this->client->sendRequest(
            TransactionType::with(
                sprintf(
                    'function () {var db = require("@arangodb").db;%s return {%s}}',
                    $actions,
                    implode(',', $return)
                ),
                array_unique($collectionsWrite),
                $params,
                array_unique($collectionsRead),
                $waitForSync
            )->toRequest($this->requestFactory, $this->streamFactory)
        );

        if (0 !== count($guards)) {
            \array_walk($guards, static function ($guard) use ($response) {
                $guard($response);
            });
        }

        $this->types = [];
        $this->transactionalTypes = [];

        return $response;
    }

    /**
     * Add type
     *
     * @param Type $type
     */
    public function add(Type $type): void
    {
        if ($type instanceof Transactional) {
            $this->transactionalTypes[] = $type;
            return;
        }
        $this->types[] = $type;
    }

    /**
     * Adds multiple types
     *
     * @param Type ...$types
     */
    public function addList(Type ...$types): void
    {
        foreach ($types as $type) {
            if ($type instanceof Transactional) {
                $this->transactionalTypes[] = $type;
                continue;
            }
            $this->types[] = $type;
        }
    }

    /**
     * Counts non transactional types
     *
     * @return int
     */
    public function countTypes(): int
    {
        return count($this->types);
    }

    /**
     * Counts transactional types
     *
     * @return int
     */
    public function countTransactionalTypes(): int
    {
        return count($this->transactionalTypes);
    }

    /**
     * Resets all types and transactional types
     */
    public function reset(): void
    {
        $this->types = [];
        $this->transactionalTypes = [];
    }
}
