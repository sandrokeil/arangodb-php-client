<?php
/**
 * Sandro Keil (https://sandro-keil.de)
 *
 * @link      http://github.com/sandrokeil/arangodb-php-client for the canonical source repository
 * @copyright Copyright (c) 2018 Sandro Keil
 * @license   http://github.com/sandrokeil/arangodb-php-client/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ArangoDb\Exception;

use LogicException as PhpLogicException;

class LogicException extends PhpLogicException implements ArangoDbException
{
    public static function notPossible(): self
    {
        return new self('Not possible at the moment, see ArangoDB docs.');
    }
}
