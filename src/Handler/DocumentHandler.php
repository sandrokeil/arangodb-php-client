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

interface DocumentHandler
{
    public const ID_SEPARATOR = '/';

    public function get(string $documentId): ResponseInterface;

    public function getById(string $collectionName, string $id): ResponseInterface;

    public function remove(string $documentId): void;

    public function removeById(string $collectionName, string $id): void;

    public function has(string $documentId): bool;

    public function hasById(string $collectionName, string $id): bool;

    public function save(string $collectionName, array $docs, int $flags = 0): string;
}
