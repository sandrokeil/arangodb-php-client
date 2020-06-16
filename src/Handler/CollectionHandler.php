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

use Psr\Http\Message\ResponseInterface;

interface CollectionHandler
{
    public function create(string $collectionName): string;

    public function has(string $collectionName): bool;

    public function drop(string $collectionName): void;

    public function count(string $collectionName): int;

    public function get(string $collectionName): ResponseInterface;

    public function truncate(string $collectionName): void;
}
