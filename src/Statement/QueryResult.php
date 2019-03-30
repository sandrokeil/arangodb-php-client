<?php
/**
 * Sandro Keil (https://sandro-keil.de)
 *
 * @link      http://github.com/sandrokeil/arangodb-php-client for the canonical source repository
 * @copyright Copyright (c) 2018-2019 Sandro Keil
 * @license   http://github.com/sandrokeil/arangodb-php-client/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ArangoDb\Statement;

interface QueryResult
{
    /**
     * Cursor id
     *
     * @return string|null
     */
    public function cursorId(): ?string;

    /**
     * A boolean indicator whether there are more results available for the cursor on the server
     *
     * @return bool
     */
    public function hasMore(): bool;

    /**
     * @return string|array|object Data
     */
    public function result();

    /**
     * Get the total number of results in the cursor if available. Does not load all data.
     *
     * @return int Total number of results in the cursor
     */
    public function resultCount(): ?int;

    /**
     * Get the full count of the cursor if available. Does not load all data.
     *
     * @return int Total number of results
     */
    public function fullCount(): ?int;

    /**
     * Returns the warnings issued during query execution
     *
     * @return array
     */
    public function warnings(): array;

    /**
     * Get the cached attribute for the result set
     *
     * @return bool Whether or not the query result was served from the AQL query cache
     */
    public function isCached(): bool;

    /**
     * Returns the number of writes executed by the query
     *
     * @return int
     */
    public function writesExecuted(): ?int;

    /**
     * Returns the number of ignored write operations from the query
     *
     * @return int
     */
    public function writesIgnored(): ?int;

    /**
     * Returns the number of documents iterated over in full scans
     *
     * @return int
     */
    public function scannedFull(): ?int;

    /**
     * Returns the number of documents iterated over in index scans
     *
     * @return int
     */
    public function scannedIndex(): ?int;

    /**
     * Returns the number of documents filtered by the query
     *
     * @return int
     */
    public function filtered(): ?int;
}
