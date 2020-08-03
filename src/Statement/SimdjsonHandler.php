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

final class SimdjsonHandler implements StreamHandler
{
    use SimdjsonStreamHandlerTrait;

    /**
     * @var mixed
     */
    private $data = [];

    public function __construct(StreamInterface $stream)
    {
        $this->data[$this->fetches] = $stream->getContents();
        $this->length = \simdjson_key_count($this->data[$this->fetches], 'result');
        $this->batchSize = $this->length;
    }

    /**
     * Return the current result row
     *
     * @return array
     */
    public function current(): array
    {
        return \simdjson_key_value(
            $this->data[$this->fetches],
            "result/" . ($this->position - ($this->batchSize * $this->fetches)),
            true
        );
    }

    public function result(): array
    {
        return \simdjson_key_value(
            $this->data[$this->fetches],
            "result",
            true
        );
    }

    public function raw(): array
    {
        return $this->data[$this->fetches];
    }

    public function completeResult()
    {
        $completeResult = [[]];

        foreach ($this->data as $result) {
            $completeResult[] = \simdjson_key_value($result, 'result', true);
        }
        return array_merge(...$completeResult);
    }

    public function appendStream(StreamInterface $stream): void
    {
        $this->data[++$this->fetches] = $stream->getContents();
        $this->length += \simdjson_key_count($this->data[$this->fetches], 'result', true);
    }

}
