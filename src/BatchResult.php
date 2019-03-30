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

use ArangoDb\Exception\InvalidArgumentException;
use ArangoDb\Exception\LogicException;
use ArangoDb\Guard\Guard;
use ArangoDb\Type\BatchType;
use Countable;
use Iterator;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;

final class BatchResult implements Countable, Iterator
{
    /**
     * responses
     *
     * @var ResponseInterface[]
     */
    private $responses = [];

    /**
     * @var ResponseFactoryInterface
     */
    private $responseFactory;

    private function __construct(ResponseFactoryInterface $responseFactory)
    {
        $this->responseFactory = $responseFactory;
    }

    public static function fromResponse(
        ResponseInterface $batchResponse,
        ResponseFactoryInterface $responseFactory,
        StreamFactoryInterface $streamFactory
    ): BatchResult {
        if ('multipart/form-data' !== ($batchResponse->getHeader('Content-Type')[0] ?? '')) {
            throw new InvalidArgumentException('Provided $batchResponse must have content type "multipart/form-data".');
        }

        $batches = explode(
            '--' . BatchType::MIME_BOUNDARY . BatchType::EOL,
            trim($batchResponse->getBody()->getContents(), '--' . BatchType::MIME_BOUNDARY . '--')
        );

        $self = new self($responseFactory);

        foreach ($batches as $batch) {
            $data = HttpHelper::parseMessage($batch);
            [$httpCode, $headers, $body] = HttpHelper::parseMessage($data[2] ?? '');

            $response = $self->responseFactory->createResponse($httpCode);

            foreach ($headers as $headerName => $header) {
                $response = $response->withAddedHeader($headerName, $header);
            }
            $response = $response->withBody($streamFactory->createStream($body));

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
            if (null !== ($response = $this->responses[$guard->contentId()] ?? null)) {
                $guard($response);
            }
        }
    }

    public function response(string $contentId): ?ResponseInterface
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

    public function current(): ResponseInterface
    {
        return current($this->responses);
    }

    public function next(): void
    {
        next($this->responses);
    }

    /**
     * @return int|string|null
     */
    public function key()
    {
        return key($this->responses);
    }

    public function valid(): bool
    {
        return $this->key() !== null;
    }

    public function rewind(): void
    {
        reset($this->responses);
    }
}
