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

class VpackStream implements StreamInterface
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
     * @var string|array
     */
    private $data;

    /**
     * Buffered json data if needed
     *
     * @var string
     */
    private $buffer;

    /**
     * @param string|array $data
     */
    public function __construct($data, bool $isVpack = false)
    {
        $this->data = $data;
        $this->isVpack = $isVpack;
    }

    public function vpack(): \Velocypack\Vpack
    {
        if (is_string($this->data)) {
            return $this->isVpack ? \Velocypack\Vpack::fromBinary($this->data) : \Velocypack\Vpack::fromJson($this->data);
        }
        return \Velocypack\Vpack::fromArray($this->data);
    }

    public function __toString()
    {
        return $this->getContents();
    }

    public function close()
    {
        $this->data = '';
    }

    public function detach()
    {
        $this->close();
    }

    public function getSize()
    {
        return strlen($this->__toString());
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
        return strlen($this->buffer) === 0;
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
        $this->seek(0);
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
            $this->buffer = $this->__toString();
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
        if (is_string($this->data)) {
            return $this->isVpack ? \Velocypack\Vpack::fromBinary($this->data)->toJson() : $this->data;
        }
        return json_encode($this->data);
    }

    public function getMetadata($key = null)
    {
        return [
            'vpack' => true,
        ];
    }
}
