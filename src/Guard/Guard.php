<?php
/**
 * Sandro Keil (https://sandro-keil.de)
 *
 * @link      http://github.com/sandrokeil/arangodb-php-client for the canonical source repository
 * @copyright Copyright (c) 2018-2020 Sandro Keil
 * @license   http://github.com/sandrokeil/arangodb-php-client/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ArangoDb\Guard;

use Psr\Http\Message\ResponseInterface;

interface Guard
{
    public function __invoke(ResponseInterface $response): void;

    /**
     * Content id is used for batch requests. Null means use guard for all responses.
     *
     * @return string|null
     */
    public function contentId(): ?string;
}
