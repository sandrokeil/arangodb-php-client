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

use ArangoDb\Util\Json;
use Psr\Http\Message\StreamInterface;

class ArrayStreamHandler implements StreamHandler
{
    use ArrayAccessStreamHandlerTrait;

    /**
     * @var mixed
     */
    private $data = [];

    public function __construct(StreamInterface $stream)
    {
        $this->data[$this->fetches] = Json::decode($stream->getContents());
        $this->length = count($this->data[$this->fetches]['result']);
        $this->batchSize = $this->length;
    }

    /**
     * Return the current result row
     *
     * @return array
     */
    public function current(): array
    {
        return $this->data[$this->fetches]['result'][$this->position - ($this->batchSize * $this->fetches)];
    }

    public function result(): array
    {
        return $this->data[$this->fetches]['result'];
    }

    public function raw(): array
    {
        return $this->data[$this->fetches];
    }

    public function completeResult()
    {
        $completeResult = [[]];

        foreach ($this->data as $result) {
            $completeResult[] = $result['result'];
        }
        return array_merge(...$completeResult);
    }

    public function appendStream(StreamInterface $stream): void
    {
        $this->data[++$this->fetches] = Json::decode($stream->getContents());
        $this->length += count($this->data[$this->fetches]['result']);
    }
}
