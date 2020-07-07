<?php
/**
 * Sandro Keil (https://sandro-keil.de)
 *
 * @link      http://github.com/sandrokeil/arangodb-php-client for the canonical source repository
 * @copyright Copyright (c) 2018-2020 Sandro Keil
 * @license   http://github.com/sandrokeil/arangodb-php-client/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ArangoDb\Http;

use ArangoDb\Type\Type;
use Psr\Http\Message\ResponseInterface;

interface TransactionSupport extends TypeSupport
{
    /**
     * Sends types and transactional types. Type responses and transaction response are validated via guards if provided
     * to a type. You can also manually validate the transaction response but not the non transaction response.
     *
     * @param array $params
     * @param bool $waitForSync
     * @return ResponseInterface
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function send(array $params = [], bool $waitForSync = false): ResponseInterface;

    /**
     * Add type
     *
     * @param Type $type
     */
    public function add(Type $type): void;

    /**
     * Adds multiple types
     *
     * @param Type ...$types
     */
    public function addList(Type ...$types): void;

    /**
     * Counts non transactional types
     *
     * @return int
     */
    public function countTypes(): int;

    /**
     * Counts transactional types
     *
     * @return int
     */
    public function countTransactionalTypes(): int;

    /**
     * Resets all types and transactional types
     */
    public function reset(): void;
}
