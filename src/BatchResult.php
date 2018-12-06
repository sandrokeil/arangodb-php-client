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

use ArangoDb\Exception\InvalidArgumentException;
use ArangoDb\Exception\LogicException;
use ArangoDb\Guard\Guard;
use ArangoDb\Http\Response;
use ArangoDb\Type\BatchType;
use Countable;
use Iterator;
use Psr\Http\Message\ResponseInterface;

final class BatchResult implements Countable, Iterator
{
    /**
     * responses
     *
     * @var ResponseInterface[]
     */
    private $responses = [];

    private function __construct()
    {
    }

    public static function fromResponse(ResponseInterface $batchResponse): BatchResult
    {
        if ('multipart/form-data' !== $batchResponse->getHeader('Content-Type')[0] ?? '') {
            throw new InvalidArgumentException('Provided $batchResponse must have content type "multipart/form-data".');
        }

        $batches = explode(
            '--' . BatchType::MIME_BOUNDARY . BatchType::EOL,
            trim($batchResponse->getBody()->getContents(), '--' . BatchType::MIME_BOUNDARY . '--')
        );

        $self = new self();

        foreach ($batches as $batch) {
            $data = HttpHelper::parseMessage($batch);
            [$httpCode, $headers, $body] = HttpHelper::parseMessage($data[2] ?? '');

            $response = new Response($httpCode, $headers);
            $response->getBody()->write($body);
            $response->getBody()->rewind();

            if (isset($data[1]['Content-Id'][0])) {
                $self->responses[$data[1]['Content-Id'][0]] = $response;
            } else {
                $self->responses[] = $response;
            }
        }
        return $self;
    }

    public function validateBatch(BatchType $batch): void
    {
        $guards = $batch->guards();

        if ($guards === null) {
            throw new LogicException('No guards are provided in Batch.');
        }

        $this->validate(... $guards);
    }

    public function validate(Guard ...$guards): void
    {
        foreach ($guards as $guard) {
            if ($guard->contentId() === null) {
                foreach ($this->responses as $response) {
                    $guard($response);
                }
                continue;
            }
            if ($response = $this->responses[$guard->contentId()] ?? null) {
                $guard($response);
            }
        }
    }

    public function response($contentId): ?ResponseInterface
    {
        return $this->responses[$contentId] ?? null;
    }

    public function responses(): array
    {
        return $this->responses;
    }

    public function count(): int
    {
        return count($this->responses);
    }

    public function current()
    {
        return current($this->responses);
    }

    public function next()
    {
        next($this->responses);
    }

    public function key()
    {
        return key($this->responses);
    }

    public function valid()
    {
        return $this->key() !== null;
    }

    public function rewind()
    {
        reset($this->responses);
    }
}
