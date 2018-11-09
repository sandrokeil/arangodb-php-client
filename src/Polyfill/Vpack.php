<?php
/**
 * Sandro Keil (https://sandro-keil.de)
 *
 * @link      http://github.com/sandrokeil/arangodb-php-client for the canonical source repository
 * @copyright Copyright (c) 2018 Sandro Keil
 * @license   http://github.com/sandrokeil/arangodb-php-client/blob/master/LICENSE.md New BSD License
 */

namespace Velocypack;

use ArrayAccess;
use Countable;

final class Vpack implements ArrayAccess, Countable
{
    /**
     * @var array
     */
    private $data;

    private function __construct(array $data)
    {
        $this->data = $data;
    }

    public static function fromArray(array $data): Vpack
    {
        return new self($data);
    }

    public static function fromJson(string $data): Vpack
    {
        return new self(json_decode($data, true));
    }

    public static function fromBinary(string $data): Vpack
    {
        return new self(json_decode($data, true));
    }

    public function toJson(): string
    {
        return json_encode($this->data);
    }

    public function toArray(): array
    {
        return $this->data;
    }

    public function toBinary(): string
    {
        return json_encode($this->data);
    }

    public function toHex(): string
    {
        // TODO
    }

    public function append(Vpack $data)
    {
        $this->data = array_merge($this->data, $data->toArray());
    }

    public function count()
    {
        return count($this->data);
    }

    /**
     * Returns whether the requested key exists
     *
     * @param  mixed $key
     * @return bool
     */
    public function __isset($key)
    {
        return $this->offsetExists($key);
    }

    /**
     * Sets the value at the specified key to value
     *
     * @param  mixed $key
     * @param  mixed $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this->offsetSet($key, $value);
    }

    /**
     * Unsets the value at the specified key
     *
     * @param  mixed $key
     * @return void
     */
    public function __unset($key)
    {
        $this->offsetUnset($key);
    }

    /**
     * Returns the value at the specified key by reference
     *
     * @param  mixed $key
     * @return mixed
     */
    public function &__get($key)
    {
        $ret = null;
        $ret =& $this->offsetGet($key);
        if (is_array($ret)) {
            $ret = self::fromArray($ret);
        }
        return $ret;
    }

    /**
     * Returns whether the requested key exists
     *
     * @param  mixed $key
     * @return bool
     */
    public function offsetExists($key)
    {
        return isset($this->data[$key]);
    }

    /**
     * Returns the value at the specified key
     *
     * @param  mixed $key
     * @return mixed
     */
    public function &offsetGet($key)
    {
        $ret = null;
        if (! $this->offsetExists($key)) {
            return $ret;
        }
        $ret =& $this->data[$key];
        if (is_array($ret)) {
            $ret = self::fromArray($ret);
        }
        return $ret;
    }

    /**
     * Sets the value at the specified key to value
     *
     * @param  mixed $key
     * @param  mixed $value
     * @return void
     */
    public function offsetSet($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * Unsets the value at the specified key
     *
     * @param  mixed $key
     * @return void
     */
    public function offsetUnset($key)
    {
        if ($this->offsetExists($key)) {
            unset($this->data[$key]);
        }
    }
}
