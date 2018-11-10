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

use ArangoDb\Type\Transaction as TransactionType;
use ArangoDb\Type\Transactional;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;

class TransactionalClient
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * Types
     *
     * @var Transactional[]
     */
    private $types;


    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    public function send(array $params = [], bool $waitForSync = false): ResponseInterface
    {
        $actions = '';
        $collectionsWrite = [[]];
        $collectionsRead = [[]];
        $return = [];
        foreach ($this->types as $key => $type) {
            $collectionsWrite[] = $type->collectionsWrite();
            $collectionsWrite[] = $type->collectionsRead();
            // TODO multiple rIds
            $actions .= str_replace('var rId', 'var rId' . $key, $type->toJs());
            $return[] = 'rId' . $key;
        }
        $collectionsWrite = array_merge(...$collectionsWrite);
        $collectionsRead = array_merge(...$collectionsRead);

        $response = $this->client->sendRequest(
            TransactionType::with(
                sprintf(
                    "function () {var db = require('@arangodb').db;%s return {%s}}",
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

        return $response;
    }

    public function add(Transactional $type): void
    {
        $this->types[] = $type;
    }

    public function addList(Transactional ...$types): void
    {
        $this->types = array_merge($this->types, $types);
    }
}
