<?php
/**
 * Sandro Keil (https://sandro-keil.de)
 *
 * @link      http://github.com/sandrokeil/arangodb-php-client for the canonical source repository
 * @copyright Copyright (c) 2018 Sandro Keil
 * @license   http://github.com/sandrokeil/arangodb-php-client/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ArangoDb\Type;

interface TransactionType extends GuardSupport
{
    /**
     * @see https://docs.arangodb.com/3.3/HTTP/Transaction/#execute-transaction
     * @see https://docs.arangodb.com/3.3/Manual/Transactions/TransactionInvocation.html#execute-transaction
     *
     * @param string $action
     * @param array $write
     * @param array $params
     * @param array $read
     * @param bool $waitForSync
     * @return TransactionType
     */
    public static function with(
        string $action,
        array $write,
        array $params = [],
        array $read = [],
        bool $waitForSync = false
    ): self;
}
