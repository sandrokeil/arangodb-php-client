/**
 * Sandro Keil (https://sandro-keil.de)
 *
 * @link      http://github.com/sandrokeil/arangodb-php-client for the canonical source repository
 * @copyright Copyright (c) 2018 Sandro Keil
 * @license   http://github.com/sandrokeil/arangodb-php-client/blob/master/LICENSE.md New BSD License
 */
namespace ArangoDb;

use ArangoDb\Exception\LogicException;
/**
 * Immutable client options array container
 */
class ClientOptions implements \ArrayAccess
{
    // connection options
    const OPTION_ENDPOINT = "endpoint";
    const OPTION_DATABASE = "database";
    const OPTION_TIMEOUT = "timeout";
    const OPTION_CONNECTION = "Connection";
    const OPTION_RECONNECT = "Reconnect";
    // connection default options
    const DEFAULT_CONNECTION = "Keep-Alive";
    const DEFAULT_TIMEOUT = 30;
    // auth options
    const OPTION_AUTH_USER = "AuthUser";
    const OPTION_AUTH_PASSWD = "AuthPasswd";
    const OPTION_AUTH_TYPE = "AuthType";
    // auth default options
    const DEFAULT_AUTH_TYPE = "Basic";
    // ssl options
    const OPTION_VERIFY_CERT = "verifyCert";
    const OPTION_VERIFY_CERT_NAME = "verifyCertName";
    const OPTION_ALLOW_SELF_SIGNED = "allowSelfSigned";
    const OPTION_CIPHERS = "ciphers";
    // ssl default options
    const DEFAULT_VERIFY_CERT = false;
    const DEFAULT_VERIFY_CERT_NAME = false;
    const DEFAULT_ALLOW_SELF_SIGNED = true;
    const DEFAULT_CIPHERS = "DEFAULT";
    /**
     * The current options
     *
     * @var array
     */
    private options;
    public function __construct(array options)
    {
        let this->options = array_merge(self::getDefaults(), options);
        this->validate();
    }

    public function toArray() -> array
    {
        return this->options;
    }

    /**
     * Returns supported authorization types
     *
     * @return string[]
     */
    private static function getSupportedAuthTypes() -> array
    {
        return ["Basic"];
    }

    /**
     * Returns supported connection types
     *
     * @return string[]
     */
    private static function getSupportedConnectionTypes() -> array
    {
        return ["Close", "Keep-Alive"];
    }

    private static function getDefaults() -> array
    {
        return [self::OPTION_ENDPOINT: "", self::OPTION_TIMEOUT: self::DEFAULT_TIMEOUT, self::OPTION_CONNECTION: self::DEFAULT_CONNECTION, self::OPTION_VERIFY_CERT: self::DEFAULT_VERIFY_CERT, self::OPTION_VERIFY_CERT_NAME: self::DEFAULT_VERIFY_CERT_NAME, self::OPTION_ALLOW_SELF_SIGNED: self::DEFAULT_ALLOW_SELF_SIGNED, self::OPTION_CIPHERS: self::DEFAULT_CIPHERS, self::OPTION_RECONNECT: false, self::OPTION_DATABASE: "_system"];
    }

    private function validate() -> void
    {
        let this->options[self::OPTION_ENDPOINT] = preg_replace(["/^http:/", "/^https:/"], ["tcp:", "ssl:"], this->options[self::OPTION_ENDPOINT]);
        if (empty(this->options[self::OPTION_ENDPOINT])) {
            throw new LogicException("Endpoint not specified");
        }
        if (!empty(this->options[self::OPTION_AUTH_TYPE]) && !in_array(this->options[self::OPTION_AUTH_TYPE], self::getSupportedAuthTypes(), true)) {
            throw new LogicException("Unsupported authorization method: " . this->options[self::OPTION_AUTH_TYPE]);
        }
        if (!empty(this->options[self::OPTION_CONNECTION]) && !in_array(this->options[self::OPTION_CONNECTION], self::getSupportedConnectionTypes(), true)) {
            throw new LogicException("Unsupported connection value: " . this->options[self::OPTION_CONNECTION]);
        }
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet(var offset, var value) -> void
    {
        throw new LogicException("Immutable object. Value for \"" . offset . "\" not set.");
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists(var offset) -> bool
    {
        return isset(this->options[offset]);
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset(var offset) -> void
    {
        throw new LogicException("Immutable object. Value for \"" . offset . "\" not unset.");
    }

    /**
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet(var offset)
    {
        if (!array_key_exists(offset, this->options)) {
            throw new LogicException("Invalid option " . offset);
        }
        return this->options[offset];
    }

}