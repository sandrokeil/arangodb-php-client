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

use ArangoDb\Request;

trait ToHttpTrait
{
    private $httpProtocol = 'HTTP/1.1';

    protected function buildAppendBatch(string $method, string $url, array $data, array $queryParams = []): array
    {
        return [\strtolower($method), $url, $data, $queryParams];
    }
}
