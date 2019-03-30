<?php
/**
 * Sandro Keil (https://sandro-keil.de)
 *
 * @link      http://github.com/sandrokeil/arangodb-php-client for the canonical source repository
 * @copyright Copyright (c) 2018-2019 Sandro Keil
 * @license   http://github.com/sandrokeil/arangodb-php-client/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ArangoDb;

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
}
