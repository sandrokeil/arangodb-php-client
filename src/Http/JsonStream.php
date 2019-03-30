<?php
/**
 * Sandro Keil (https://sandro-keil.de)
 *
 * @link      http://github.com/sandrokeil/arangodb-php-client for the canonical source repository
 * @copyright Copyright (c) 2018-2019 Sandro Keil
 * @license   http://github.com/sandrokeil/arangodb-php-client/blob/master/LICENSE.md New BSD License
 */

namespace ArangoDb\Http;

use ArangoDb\Exception\RuntimeException;
use ArangoDb\Util\Json;
use Psr\Http\Message\StreamInterface;

final class JsonStream implements StreamInterface
{
    /**
     * Original data
     *
     * @var string|array
     */
    private $data;

    /**
     * Buffered json data if needed
     *
     * @var string|null
     */
    private $buffer;

    /**
     * @var int
     */
    private $size;

    /**
     * @param string|array $data
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    public function toJson(): string
    {
        return $this->getContents();
    }

    public function toArray(): array
    {
        if (is_string($this->data)) {
            return Json::decode($this->data);
        }
        return $this->data;
    }

    public function __toString()
    {
        return $this->getContents();
    }

    public function close(): void
    {
        $this->data = '';
        $this->buffer = null;
    }

    public function detach()
    {
        $this->close();
        return null;
    }

    public function getSize(): int
    {
        if ($this->size === null) {
            $this->size = strlen($this->getContents());
        }

        return $this->size;
    }

    public function tell(): int
    {
        throw new RuntimeException('Cannot determine the position of a JsonStream');
    }

    public function eof(): bool
    {
        if ($this->buffer === null) {
            return false;
        }
        return $this->buffer === '';
    }

    public function isSeekable(): bool
    {
        return false;
    }

    /**
     * @param int $offset
     * @param int $whence
     */
    public function seek($offset, $whence = SEEK_SET): void
    {
        throw new RuntimeException('Cannot seek a JsonStream');
    }

    public function rewind(): void
    {
        $this->buffer = null;
    }

    public function isWritable(): bool
    {
        return false;
    }

    public function write($string): int
    {
        throw new RuntimeException('Cannot write a JsonStream');
    }

    public function isReadable(): bool
    {
        return true;
    }

    /**
     * @param int $length
     * @return string
     */
    public function read($length): string
    {
        if ($this->buffer === null) {
            $this->buffer = $this->getContents();
        }
        $currentLength = strlen($this->buffer);

        if ($length >= $currentLength) {
            // No need to slice the data because we don't have enough data.
            $result = $this->buffer;
            $this->buffer = '';
        } else {
            // Slice up the result to provide a subset of the data.
            $result = substr($this->buffer, 0, $length);
            $this->buffer = substr($this->buffer, $length);
        }

        return $result;
    }

    public function getContents(): string
    {
        if (is_string($this->data)) {
            return $this->data;
        }
        return Json::encode($this->data);
    }

    /**
     * @param string|null $key
     * @return array|mixed|null
     */
    public function getMetadata($key = null)
    {
        $metadata = [
            'wrapper_data' => ['string'],
            'wrapper_type' => 'string',
            'stream_type' => 'string',
            'mode' => 'r',
            'unread_bytes' => $this->getSize() - strlen($this->buffer ?? ''),
            'seekable' => $this->isSeekable(),
            'timeout' => false,
            'blocked' => false,
            'eof' => $this->eof()
        ];

        if ($key === null) {
            return $metadata;
        }
        return $metadata[$key] ?? null;
    }
}
