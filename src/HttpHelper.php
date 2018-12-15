<?php
/**
 * Sandro Keil (https://sandro-keil.de)
 *
 * @link      http://github.com/sandrokeil/arangodb-php-client for the canonical source repository
 * @copyright Copyright (c) 2018 Sandro Keil
 * @license   http://github.com/sandrokeil/arangodb-php-client/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ArangoDb;

use ArangoDb\Http\VpackStream;
use Psr\Http\Message\ResponseInterface;
use Velocypack\Vpack;

final class HttpHelper
{
    /**
     * Separator between header and body
     */
    private const BODY_SEPARATOR = "\r\n\r\n";

    /**
     * Splits the message in HTTP status code, headers and body.
     *
     * @param string $message
     * @return array Values are HTTP status code, PSR-7 headers and body
     */
    public static function parseMessage(string $message): array
    {
        $startLine = null;
        $headers = [];
        [$headerLines, $body] = explode(self::BODY_SEPARATOR, $message, 2);
        $headerLines = explode("\n", $headerLines);

        foreach ($headerLines as $header) {
            // Parse message headers
            if ($startLine === null) {
                $startLine = explode(' ', $header, 3);
                continue;
            }
            $parts = explode(':', $header, 2);
            $key = trim($parts[0]);
            $value = isset($parts[1]) ? trim($parts[1]) : '';

            if (! isset($headers[$key])) {
                $headers[$key] = [];
            }
            $headers[$key][] = $value;
        }

        return [
            (int) ($startLine[1] ?? 0),
            $headers,
            $body,
        ];
    }


    /**
     * @param ResponseInterface $response
     * @param string|null $key
     * @return string|bool|int|float
     */
    public static function responseContentAsJson(ResponseInterface $response, string $key = null)
    {
        $body = $response->getBody();

        if ($body instanceof VpackStream) {
            if ($key === null) {
                return $body->vpack()->toJson();
            }
            $value = $body->vpack()->access($key);

            if ($value instanceof Vpack) {
                $value = $value->toJson();
            }

            return $value;
        }
        if ($key === null) {
            return $body->getContents();
        }
        // TODO check key
        $value = \json_decode($body->getContents())->{$key};
        if (! \is_scalar($value)) {
            $value = \json_encode($value);
        }

        return $value;
    }

    /**
     * @param ResponseInterface $response
     * @param string|null $key
     * @return string|bool|int|float|array
     */
    public static function responseContentAsArray(ResponseInterface $response, string $key = null)
    {
        $body = $response->getBody();

        if ($body instanceof VpackStream) {
            if ($key === null) {
                return $body->vpack()->toArray();
            }
            $value = $body->vpack()->access($key);

            if ($value instanceof Vpack) {
                $value = $value->toArray();
            }

            return $value;
        }
        if ($key === null) {
            return $body->getContents();
        }
        // TODO check key
        return \json_decode($body->getContents(), true)[$key] ?? null;
    }
}
