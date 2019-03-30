<?php
/**
 * Sandro Keil (https://sandro-keil.de)
 *
 * @link      http://github.com/sandrokeil/arangodb-php-client for the canonical source repository
 * @copyright Copyright (c) 2018-2019 Sandro Keil
 * @license   http://github.com/sandrokeil/arangodb-php-client/blob/master/LICENSE.md New BSD License
 */

namespace ArangoDb;

use ArangoDb\Exception\LogicException;

/**
 * Immutable client options array container
 */
final class ClientOptions implements \ArrayAccess
{
    // connection options
    public const OPTION_ENDPOINT = 'endpoint';
    public const OPTION_DATABASE = 'database';
    public const OPTION_TIMEOUT = 'timeout';
    public const OPTION_CONNECTION = 'Connection';
    public const OPTION_RECONNECT = 'Reconnect';

    // connection default options
    public const DEFAULT_CONNECTION = 'Keep-Alive';
    public const DEFAULT_TIMEOUT = 30;

    // auth options
    public const OPTION_AUTH_USER = 'AuthUser';
    public const OPTION_AUTH_PASSWD = 'AuthPasswd';
    public const OPTION_AUTH_TYPE = 'AuthType';

    // auth default options
    public const DEFAULT_AUTH_TYPE = 'Basic';

    // ssl options
    public const OPTION_VERIFY_CERT = 'verifyCert';
    public const OPTION_VERIFY_CERT_NAME = 'verifyCertName';
    public const OPTION_ALLOW_SELF_SIGNED = 'allowSelfSigned';
    public const OPTION_CIPHERS = 'ciphers';

    // ssl default options
    public const DEFAULT_VERIFY_CERT = false;
    public const DEFAULT_VERIFY_CERT_NAME = false;
    public const DEFAULT_ALLOW_SELF_SIGNED = true;
    public const DEFAULT_CIPHERS = 'DEFAULT';

    /**
     * The current options
     *
     * @var array
     */
    private $options;

    public function __construct(array $options)
    {
        $this->options = array_merge(self::getDefaults(), $options);
        $this->validate();
    }

    public function toArray(): array
    {
        return $this->options;
    }

    /**
     * Returns supported authorization types
     *
     * @return string[]
     */
    private static function getSupportedAuthTypes(): array
    {
        return ['Basic'];
    }

    /**
     * Returns supported connection types
     *
     * @return string[]
     */
    private static function getSupportedConnectionTypes(): array
    {
        return ['Close', 'Keep-Alive'];
    }

    private static function getDefaults(): array
    {
        return [
            self::OPTION_ENDPOINT                => '',
            self::OPTION_TIMEOUT                 => self::DEFAULT_TIMEOUT,
            self::OPTION_CONNECTION              => self::DEFAULT_CONNECTION,
            self::OPTION_VERIFY_CERT             => self::DEFAULT_VERIFY_CERT,
            self::OPTION_VERIFY_CERT_NAME        => self::DEFAULT_VERIFY_CERT_NAME,
            self::OPTION_ALLOW_SELF_SIGNED       => self::DEFAULT_ALLOW_SELF_SIGNED,
            self::OPTION_CIPHERS                 => self::DEFAULT_CIPHERS,
            self::OPTION_RECONNECT               => false,
            self::OPTION_DATABASE                => '_system',
        ];
    }

    private function validate(): void
    {
        $this->options[self::OPTION_ENDPOINT] = preg_replace(
            ['/^http:/', '/^https:/'],
            ['tcp:', 'ssl:'],
            $this->options[self::OPTION_ENDPOINT]
        );

        if (! isset($this->options[self::OPTION_ENDPOINT])) {
            throw new LogicException('Endpoint not specified');
        }

        if (isset($this->options[self::OPTION_AUTH_TYPE])
            && ! in_array($this->options[self::OPTION_AUTH_TYPE], self::getSupportedAuthTypes(), true)
        ) {
            throw new LogicException('Unsupported authorization method: ' . $this->options[self::OPTION_AUTH_TYPE]);
        }

        if (isset($this->options[self::OPTION_CONNECTION])
            && ! in_array($this->options[self::OPTION_CONNECTION], self::getSupportedConnectionTypes(), true)
        ) {
            throw new LogicException('Unsupported connection value: ' . $this->options[self::OPTION_CONNECTION]);
        }
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value): void
    {
        throw new LogicException('Immutable object. Value for "' . $offset . '" not set.');
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return isset($this->options[$offset]);
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset): void
    {
        throw new LogicException('Immutable object. Value for "' . $offset . '" not unset.');
    }

    /**
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        if (! array_key_exists($offset, $this->options)) {
            throw new LogicException('Invalid option ' . $offset);
        }

        return $this->options[$offset];
    }
}
