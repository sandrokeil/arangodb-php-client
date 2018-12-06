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

use ArangoDb\Type\Batch;
use ArangoDb\Type\GuardSupport;
use ArangoDb\Type\Transaction as TransactionType;
use ArangoDb\Type\Transactional;
use ArangoDb\Type\Type;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class TransactionalClient implements ClientInterface
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
    private $types;

    /**
     * Types
     *
     * @var Transactional[]
     */
    private $transactionalTypes;


    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        return $this->client->sendRequest($request);
    }

    public function send(array $params = [], bool $waitForSync = false): ResponseInterface
    {
        $actions = '';
        $collectionsWrite = [[]];
        $collectionsRead = [[]];
        $return = [];
        foreach ($this->transactionalTypes as $key => $type) {
            $collectionsWrite[] = $type->collectionsWrite();
            $collectionsWrite[] = $type->collectionsRead();
            if ($type instanceof GuardSupport
                && ($guard = $type->guard())
                && $contentId = $guard->contentId()
            ) {
                $key = $guard->contentId();
            }
            $actions .= str_replace('var rId', 'var rId' . $key, $type->toJs());
            $return[] = 'rId' . $key;
        }
        $collectionsWrite = array_merge(...$collectionsWrite);
        $collectionsRead = array_merge(...$collectionsRead);

        if (! empty($this->types)) {
            $batch = Batch::fromTypes(...$this->types);
            $responseBatch = $this->client->sendRequest($batch->toRequest());
            BatchResult::fromResponse($responseBatch)->validateBatch($batch);
        }

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

        $this->types = [];
        $this->transactionalTypes = [];

        return $response;
    }

    public function add(Type $type): void
    {
        if ($type instanceof Transactional) {
            $this->transactionalTypes[] = $type;
            return;
        }
        $this->types[] = $type;
    }

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
}
