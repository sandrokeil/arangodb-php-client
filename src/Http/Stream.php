<?php
/**
 * Sandro Keil (https://sandro-keil.de)
 *
 * @link      http://github.com/sandrokeil/arangodb-php-client for the canonical source repository
 * @copyright Copyright (c) 2018 Sandro Keil
 * @license   http://github.com/sandrokeil/arangodb-php-client/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ArangoDb\Http;

use ArangoDb\Exception;
use Psr\Http\Message\StreamInterface;

use function array_key_exists;
use function fclose;
use function feof;
use function fopen;
use function fread;
use function fseek;
use function fstat;
use function ftell;
use function fwrite;
use function get_resource_type;
use function is_int;
use function is_resource;
use function is_string;
use function restore_error_handler;
use function set_error_handler;
use function stream_get_contents;
use function stream_get_meta_data;
use function strstr;

use const E_WARNING;
use const SEEK_SET;

/**
 * Implementation of PSR HTTP streams
 *
 * Code is largely lifted from the Zend\Diactoros\Stream implementation in
 * Zend Diactoros, released with the copyright and license below.
 *
 * @see       https://github.com/zendframework/zend-diactoros for the canonical source repository
 * @copyright Copyright (c) 2015-2018 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-diactoros/blob/master/LICENSE.md New BSD License
 */
final class Stream implements StreamInterface
{
    /**
     * @var resource|null
     */
    private $handle;

    /**
     * @var string|resource
     */
    private $stream;

    /**
     * @param string|resource $stream
     * @param string $mode Mode with which to open stream
     * @throws Exception\InvalidArgumentException
     */
    public function __construct($stream, string $mode = 'r')
    {
        $this->setStream($stream, $mode);
    }

    /**
     * {@inheritdoc}
     */
    public function __toString() : string
    {
        if (! $this->isReadable()) {
            return '';
        }

        try {
            if ($this->isSeekable()) {
                $this->rewind();
            }

            return $this->getContents();
        } catch (Exception\RuntimeException $e) {
            return '';
        }
    }

    /**
     * {@inheritdoc}
     */
    public function close() : void
    {
        if (! $this->handle) {
            return;
        }

        $handle = $this->detach();
        fclose($handle);
    }

    /**
     * {@inheritdoc}
     */
    public function detach()
    {
        $handle = $this->handle;
        $this->handle = null;
        return $handle;
    }

    /**
     * Attach a new stream/resource to the instance.
     *
     * @param string|resource $handle
     * @param string $mode
     * @throws Exception\InvalidArgumentException for stream identifier that cannot be
     *     cast to a resource
     * @throws Exception\InvalidArgumentException for non-resource stream
     */
    public function attach($handle, string $mode = 'r') : void
    {
        $this->setStream($handle, $mode);
    }

    /**
     * {@inheritdoc}
     */
    public function getSize() : ?int
    {
        if (null === $this->handle) {
            return null;
        }

        $stats = fstat($this->handle);
        if ($stats !== false) {
            return $stats['size'];
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function tell() : int
    {
        if (! $this->handle) {
            throw Exception\UntellableStreamException::dueToMissingResource();
        }

        $result = ftell($this->handle);
        if (! is_int($result)) {
            throw Exception\UntellableStreamException::dueToPhpError();
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function eof() : bool
    {
        if (! $this->handle) {
            return true;
        }

        return feof($this->handle);
    }

    /**
     * {@inheritdoc}
     */
    public function isSeekable() : bool
    {
        if (! $this->handle) {
            return false;
        }

        $meta = stream_get_meta_data($this->handle);
        return $meta['seekable'];
    }

    /**
     * {@inheritdoc}
     */
    public function seek($offset, $whence = SEEK_SET) : void
    {
        if (! $this->handle) {
            throw Exception\UnseekableStreamException::dueToMissingResource();
        }

        if (! $this->isSeekable()) {
            throw Exception\UnseekableStreamException::dueToConfiguration();
        }

        $result = fseek($this->handle, $offset, $whence);

        if (0 !== $result) {
            throw Exception\UnseekableStreamException::dueToPhpError();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rewind() : void
    {
        $this->seek(0);
    }

    /**
     * {@inheritdoc}
     */
    public function isWritable() : bool
    {
        if (! $this->handle) {
            return false;
        }

        $meta = stream_get_meta_data($this->handle);
        $mode = $meta['mode'];

        return (
            strstr($mode, 'x')
            || strstr($mode, 'w')
            || strstr($mode, 'c')
            || strstr($mode, 'a')
            || strstr($mode, '+')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function write($data) : int
    {
        if (! $this->handle) {
            throw Exception\UnwritableStreamException::dueToMissingResource();
        }

        if (! $this->isWritable()) {
            throw Exception\UnwritableStreamException::dueToConfiguration();
        }

        $result = fwrite($this->handle, $data);

        if (false === $result) {
            throw Exception\UnwritableStreamException::dueToPhpError();
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function isReadable() : bool
    {
        if (! $this->handle) {
            return false;
        }

        $meta = stream_get_meta_data($this->handle);
        $mode = $meta['mode'];

        return (strstr($mode, 'r') || strstr($mode, '+'));
    }

    /**
     * {@inheritdoc}
     */
    public function read($length) : string
    {
        if (! $this->handle) {
            throw Exception\UnreadableStreamException::dueToMissingResource();
        }

        if (! $this->isReadable()) {
            throw Exception\UnreadableStreamException::dueToConfiguration();
        }

        $result = fread($this->handle, $length);

        if (false === $result) {
            throw Exception\UnreadableStreamException::dueToPhpError();
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getContents() : string
    {
        if (! $this->isReadable()) {
            throw Exception\UnreadableStreamException::dueToConfiguration();
        }

        $result = stream_get_contents($this->handle);
        if (false === $result) {
            throw Exception\UnreadableStreamException::dueToPhpError();
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadata($key = null)
    {
        if (null === $key) {
            return stream_get_meta_data($this->handle);
        }

        $metadata = stream_get_meta_data($this->handle);
        if (! array_key_exists($key, $metadata)) {
            return null;
        }

        return $metadata[$key];
    }

    /**
     * Set the internal stream resource.
     *
     * @param string|resource $stream String stream target or stream resource.
     * @param string $mode Resource mode for stream target.
     * @throws Exception\InvalidArgumentException for invalid streams or resources.
     */
    private function setStream($stream, string $mode = 'r') : void
    {
        $error    = null;
        $handle = $stream;

        if (is_string($stream)) {
            set_error_handler(function ($e) use (&$error) {
                if ($e !== E_WARNING) {
                    return;
                }

                $error = $e;
            });
            $handle = fopen($stream, $mode);
            restore_error_handler();
        }

        if ($error) {
            throw new Exception\InvalidArgumentException('Invalid stream reference provided');
        }

        if (! is_resource($handle) || 'stream' !== get_resource_type($handle)) {
            throw new Exception\InvalidArgumentException(
                'Invalid stream provided; must be a string stream identifier or stream resource'
            );
        }

        if ($stream !== $handle) {
            $this->stream = $stream;
        }

        $this->handle = $handle;
    }
}
