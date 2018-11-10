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

interface CursorType extends Type
{
    /**
     * @see https://docs.arangodb.com/3.3/HTTP/AqlQueryCursor/AccessingCursors.html
     *
     * @param string $query
     * @param array $bindVars
     * @param int|null $batchSize
     * @param bool $count
     * @param bool|null $cache
     * @param array $options
     * @return CursorType
     */
    public static function create(
        string $query,
        array $bindVars = [],
        int $batchSize = null,
        bool $count = false,
        bool $cache = null,
        array $options = []
    ): self;

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/AqlQueryCursor/AccessingCursors.html#delete-cursor
     *
     * @param string $cursorId
     * @return CursorType
     */
    public static function delete(string $cursorId): self;

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/AqlQueryCursor/AccessingCursors.html#read-next-batch-from-cursor
     *
     * @param string $cursorId
     * @return CursorType
     */
    public static function nextBatch(string $cursorId): self;
}
