<?php
/**
 * Sandro Keil (https://sandro-keil.de)
 *
 * @link      http://github.com/sandrokeil/arangodb-php-client for the canonical source repository
 * @copyright Copyright (c) 2018-2020 Sandro Keil
 * @license   http://github.com/sandrokeil/arangodb-php-client/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ArangoDb\Statement;

use Psr\Http\Message\StreamInterface;

class SimdjsonStreamHandlerFactory implements StreamHandlerFactoryInterface
{
    public function createStreamHandler(StreamInterface $body): StreamHandler
    {
        return new SimdjsonHandler($body);
    }
}
