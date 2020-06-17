<?php
/**
 * Sandro Keil (https://sandro-keil.de)
 *
 * @link      http://github.com/sandrokeil/arangodb-php-client for the canonical source repository
 * @copyright Copyright (c) 2018-2020 Sandro Keil
 * @license   http://github.com/sandrokeil/arangodb-php-client/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ArangoDb\Type;

interface Transactional extends Type
{
    /**
     * @return array
     */
    public function collectionsRead(): array;

    /**
     * @return array
     */
    public function collectionsWrite(): array;

    /**
     * Returns the JS which is executed in transaction.
     *
     * If you use TransactionalClient ensure that your statement starts with "var rId =" because it is extended with a
     * number to check results later.
     *
     * @return string
     */
    public function toJs(): string;
}
