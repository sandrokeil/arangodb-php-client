<?php
/**
 * Sandro Keil (https://sandro-keil.de)
 *
 * @link      http://github.com/sandrokeil/arangodb-php-client for the canonical source repository
 * @copyright Copyright (c) 2018-2020 Sandro Keil
 * @license   http://github.com/sandrokeil/arangodb-php-client/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ArangoDb\Statement;

trait SimdjsonStreamHandlerTrait
{
    /**
     * Current position in result set iteration (zero-based)
     *
     * @var int
     */
    private $position = 0;

    /**
     * Number of HTTP calls that were made to build the cursor result
     *
     * @var int
     */
    private $fetches = 0;

    /**
     * Total length of result set (in number of documents)
     *
     * @var int
     */
    private $length;

    /**
     * @var int
     */
    private $batchSize;

    public function cursorId(): ?string
    {
        return \simdjson_key_value($this->data[$this->fetches], 'id', true);
    }

    public function hasMore(): bool
    {
        return \simdjson_key_value($this->data[$this->fetches], 'hasMore', true);
    }

    public function resultCount(): ?int
    {
        return \simdjson_key_value($this->data[$this->fetches], 'count', true);
    }

    /**
     * Get the total number of current loaded results.
     *
     * @return int Total number of laoded results
     */
    public function count()
    {
        return $this->length;
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function key(): int
    {
        return $this->position;
    }

    public function next(): void
    {
        $this->position++;
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        return $this->position <= $this->length - 1;
    }

    public function writesExecuted(): ?int
    {
        return \simdjson_key_value($this->data[$this->fetches], "extra/stats/writesExecuted", true);
    }

    public function writesIgnored(): ?int
    {
        return \simdjson_key_value($this->data[$this->fetches], "extra/stats/writesIgnored", true);
    }

    public function scannedFull(): ?int
    {
        return \simdjson_key_value($this->data[$this->fetches], "extra/stats/scannedFull", true);
    }

    public function scannedIndex(): ?int
    {
        return \simdjson_key_value($this->data[$this->fetches], "extra/stats/scannedIndex", true);
    }

    public function filtered(): ?int
    {
        return \simdjson_key_value($this->data[$this->fetches], "extra/stats/filtered", true);
    }

    public function fullCount(): ?int
    {
        return \simdjson_key_value($this->data[$this->fetches], "extra/stats/fullCount", true);
    }

    public function warnings(): array
    {
        return \simdjson_key_value($this->data[$this->fetches], "extra/warnings", true);
    }

    public function isCached(): bool
    {
        return \simdjson_key_value($this->data[$this->fetches], "cached", true);
    }
}
