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

trait ArrayAccessStreamHandlerTrait
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

    public function result(): array
    {
        return $this->data[$this->fetches]['result'];
    }

    public function cursorId(): ?string
    {
        return $this->data[$this->fetches]['id'] ?? null;
    }

    public function hasMore(): bool
    {
        return $this->data[$this->fetches]['hasMore'] ?? false;
    }

    public function resultCount(): ?int
    {
        return $this->data[$this->fetches]['count'] ?? null;
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

    /**
     * Return the current result row
     *
     * @return array
     */
    public function current(): array
    {
        return $this->data[$this->fetches]['result'][$this->position - ($this->batchSize * $this->fetches)];
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
        if ($this->position <= $this->length - 1) {
            // we have more results than the current position is
            return true;
        }

        return ($this->position <= $this->length - 1);
    }

    public function writesExecuted(): ?int
    {
        return $this->data[$this->fetches]['extra']['stats']['writesExecuted'] ?? null;
    }

    public function writesIgnored(): ?int
    {
        return $this->data[$this->fetches]['extra']['stats']['writesIgnored'] ?? null;
    }

    public function scannedFull(): ?int
    {
        return $this->data[$this->fetches]['extra']['stats']['scannedFull'] ?? null;
    }

    public function scannedIndex(): ?int
    {
        return $this->data[$this->fetches]['extra']['stats']['scannedIndex'] ?? null;
    }

    public function filtered(): ?int
    {
        return $this->data[$this->fetches]['extra']['stats']['filtered'] ?? null;
    }

    public function fullCount(): ?int
    {
        return $this->data[$this->fetches]['extra']['stats']['fullCount'] ?? null;
    }

    public function warnings(): array
    {
        return $this->data[$this->fetches]['extra']['warnings'] ?? [];
    }

    public function isCached(): bool
    {
        return $this->data[$this->fetches]['cached'] ?? false;
    }
}
