<?php
/**
 * Sandro Keil (https://sandro-keil.de)
 *
 * @link      http://github.com/sandrokeil/arangodb-php-client for the canonical source repository
 * @copyright Copyright (c) 2018-2019 Sandro Keil
 * @license   http://github.com/sandrokeil/arangodb-php-client/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ArangoDb\Statement;

use Countable;
use Iterator;
use Psr\Http\Message\StreamInterface;

interface StreamHandler extends QueryResult, Iterator, Countable
{
    /**
     * Append stream data
     *
     * @param StreamInterface $stream
     */
    public function appendStream(StreamInterface $stream): void;

    /**
     * @return string|array|object Data
     */
    public function completeResult();
}
