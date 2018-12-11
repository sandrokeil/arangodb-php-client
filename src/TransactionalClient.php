<?php
/**
 * Sandro Keil (https://sandro-keil.de)
 *
 * @link      http://github.com/sandrokeil/arangodb-php-client for the canonical source repository
 * @copyright Copyright (c) 2018 Sandro Keil
 * @license   http://github.com/sandrokeil/arangodb-php-client/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ArangoDb;

use ArangoDb\Http\Response;
use ArangoDb\Type\Batch;
use ArangoDb\Type\GuardSupport;
use ArangoDb\Type\Transaction as TransactionType;
use ArangoDb\Type\Transactional;
use ArangoDb\Type\Type;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class TransactionalClient implements ClientInterface
{
    /**
     * @var ClientInterface
     */
    private $client;

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
     * TransactionalClient constructor.
     * @param ClientInterface $client
     */
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
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
        $actions = '';
        $collectionsWrite = [[]];
        $collectionsRead = [[]];
        $return = [];
        $guards = [];

        if (! empty($this->types)) {
            $batch = Batch::fromTypes(...$this->types);
            $responseBatch = $this->client->sendRequest($batch->toRequest());
            BatchResult::fromResponse($responseBatch)->validateBatch($batch);
        }

        if (empty($this->transactionalTypes)) {
            return new Response(StatusCodeInterface::STATUS_OK);
        }

        foreach ($this->transactionalTypes as $key => $type) {
            $collectionsWrite[] = $type->collectionsWrite();
            $collectionsWrite[] = $type->collectionsRead();
            if ($type instanceof GuardSupport
                && ($guard = $type->guard())
                && $contentId = $guard->contentId()
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
            )->toRequest()
        );

        if (! empty($guards)) {
            \array_walk($guards, function ($guard) use ($response) {
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
