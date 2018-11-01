<?php
/**
 * Sandro Keil (https://sandro-keil.de)
 *
 * @link      http://github.com/sandrokeil/arangodb-php-client for the canonical source repository
 * @copyright Copyright (c) 2018 Sandro Keil
 * @license   http://github.com/sandrokeil/arangodb-php-client/blob/master/LICENSE.md New BSD License
 */

namespace ArangoDb;

use Psr\Http\Message\StreamInterface;
use Velocypack\Vpack;

final class VpackStream implements StreamInterface
{
    /**
     * Is string a vpack binary
     *
     * @var bool
     */
    private $isVpack;

    /**
     * Original data
     *
     * @var string|array|Vpack
     */
    private $data;

    /**
     * Buffered json data if needed
     *
     * @var string
     */
    private $buffer;

    /**
     * @var int
     */
    private $size;

    /**
     * @param string|array $data
     */
    public function __construct($data, bool $isVpack = false)
    {
        $this->data = $data;
        $this->isVpack = $isVpack;
    }

    public function vpack(): Vpack
    {
        if ($this->data instanceof Vpack) {
            return $this->data;
        }
        if (is_string($this->data)) {
            $this->data = $this->isVpack
                ? Vpack::fromBinary($this->data)
                : Vpack::fromJson($this->data);
        } else {
            $this->data = Vpack::fromArray($this->data);
        }
        return $this->data;
    }

    public function __toString()
    {
        return $this->getContents();
    }

    public function close()
    {
        $this->data = '';
        $this->buffer = null;
    }

    public function detach()
    {
        $this->close();
    }

    public function getSize()
    {
        if ($this->size === null) {
            $this->size = strlen($this->getContents());
        }

        return $this->size;
    }

    public function tell()
    {
        throw new \RuntimeException('Cannot determine the position of a VpackStream');
    }

    public function eof()
    {
        if ($this->buffer === null) {
            return false;
        }
        return $this->buffer === '';
    }

    public function isSeekable()
    {
        return false;
    }

    public function seek($offset, $whence = SEEK_SET)
    {
        throw new \RuntimeException('Cannot seek a VpackStream');
    }

    public function rewind()
    {
        $this->buffer = null;
    }

    public function isWritable()
    {
        return false;
    }

    public function write($string)
    {
        throw new \RuntimeException('Cannot write a VpackStream');
    }

    public function isReadable()
    {
        return true;
    }

    public function read($length)
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

    public function getContents()
    {
        if ($this->data instanceof Vpack) {
            return $this->data->toJson();
        }

        if ($this->isVpack === true && is_string($this->data)) {
            $this->data = Vpack::fromBinary($this->data);
            return $this->data->toJson();
        }

        if (! is_string($this->data)) {
            $this->data = json_encode($this->data);
        }
        return $this->data;
    }

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
