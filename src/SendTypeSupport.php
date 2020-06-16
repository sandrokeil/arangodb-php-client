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

use ArangoDb\Type\Type;
use Psr\Http\Message\ResponseInterface;

interface SendTypeSupport
{
    /**
     * Sends the type and validates the response against the type guard if supported
     *
     * @param Type $type
     * @return ResponseInterface
     */
    public function sendType(Type $type): ResponseInterface;
}
