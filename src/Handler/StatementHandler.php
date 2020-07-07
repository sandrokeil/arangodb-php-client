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

use ArangoDb\Statement\Statement as StatementStatement;

interface StatementHandler
{
    public function create(
        string $query,
        array $bindVars = [],
        int $batchSize = null,
        bool $count = false,
        bool $cache = null,
        array $options = []
    ): StatementStatement;
}
