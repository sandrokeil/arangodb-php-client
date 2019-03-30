<?php
/**
 * Sandro Keil (https://sandro-keil.de)
 *
 * @link      http://github.com/sandrokeil/arangodb-php-client for the canonical source repository
 * @copyright Copyright (c) 2018-2019 Sandro Keil
 * @license   http://github.com/sandrokeil/arangodb-php-client/blob/master/LICENSE.md New BSD License
 */
declare(strict_types=1);

namespace ArangoDb\Util;

use ArangoDb\Exception\JsonException;

/**
 * Code is largely lifted from the Prooph\EventStore\Pdo\Util\Json implementation in
 * prooph/pdo-event-store, released with the copyright and license below.
 *
 * @see       https://github.com/prooph/pdo-event-store for the canonical source repository
 * @copyright Copyright (c) 2016-2019 prooph software GmbH <contact@prooph.de>
 * @copyright Copyright (c) 2016-2019 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 * @license   https://github.com/prooph/pdo-event-store/blob/master/LICENSE New BSD License
 */
class Json
{
    /**
     * @param mixed $value
     *
     * @return string
     *
     * @throws JsonException
     */
    public static function encode($value): string
    {
        $flags = \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES | \JSON_PRESERVE_ZERO_FRACTION;

        $json = \json_encode($value, $flags);

        if ((JSON_ERROR_NONE !== $error = \json_last_error()) || $json === false) {
            throw new JsonException(\json_last_error_msg(), $error);
        }

        return $json;
    }

    /**
     * @param string $json
     *
     * @param bool $assoc
     * @param int $depth
     * @return mixed
     *
     */
    public static function decode(string $json, bool $assoc = true, int $depth = 512)
    {
        $data = \json_decode($json, $assoc, $depth, \JSON_BIGINT_AS_STRING);

        if (JSON_ERROR_NONE !== $error = \json_last_error()) {
            throw new JsonException(\json_last_error_msg(), $error);
        }

        return $data;
    }
}
