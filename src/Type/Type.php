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

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface Type
{
    public function toRequest(): RequestInterface;

    public function toJs(): string;

    public function collectionName(): string;

    public function checkResponse(ResponseInterface $response, string $rId = null): ?int;
}
