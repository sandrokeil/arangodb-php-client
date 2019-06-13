<?php
/**
 * Sandro Keil (https://sandro-keil.de)
 *
 * @link      http://github.com/sandrokeil/arangodb-php-client for the canonical source repository
 * @copyright Copyright (c) 2018-2019 Sandro Keil
 * @license   http://github.com/sandrokeil/arangodb-php-client/blob/master/LICENSE.md New BSD License
 */

namespace ArangoDb;

use ArangoDb\Exception\ServerException;
use ArangoDb\Statement\QueryResult;
use ArangoDb\Statement\StreamHandler;
use ArangoDb\Statement\StreamHandlerFactoryInterface;
use Countable;
use Fig\Http\Message\RequestMethodInterface;
use Fig\Http\Message\StatusCodeInterface;
use Iterator;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;

final class Statement implements QueryResult, Iterator, Countable
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var RequestFactoryInterface
     */
    private $requestFactory;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var StreamHandler
     */
    private $streamHandler;

    /**
     * @var StreamHandlerFactoryInterface
     */
    private $streamHandlerFactory;

    /**
     * Number of HTTP calls that were made to build the cursor result
     *
     * @var int
     */
    private $fetches = 0;

    /**
     * Query is executed on first access
     *
     * @param ClientInterface $client - connection to be used
     * @param RequestInterface $request Cursor request
     * @param RequestFactoryInterface $requestFactory
     * @param StreamHandlerFactoryInterface $streamHandlerFactory
     */
    public function __construct(
        ClientInterface $client,
        RequestInterface $request,
        RequestFactoryInterface $requestFactory,
        StreamHandlerFactoryInterface $streamHandlerFactory
    ) {
        $this->client = $client;
        $this->request = $request;
        $this->requestFactory = $requestFactory;
        $this->streamHandlerFactory = $streamHandlerFactory;
    }

    /**
     * Fetch outstanding results from the server
     *
     * @return void
     * @throws ClientExceptionInterface
     */
    private function fetchOutstanding(): void
    {
        $request = $this->fetches === 0
            ? $this->request
            : $this->requestFactory->createRequest(
                RequestMethodInterface::METHOD_PUT,
                Url::CURSOR . '/' . $this->streamHandler->cursorId()
            );

        $request->getBody()->rewind();
        $response = $this->client->sendRequest($request);

        $httpStatusCode = $response->getStatusCode();

        if ($httpStatusCode < StatusCodeInterface::STATUS_OK
            || $httpStatusCode > StatusCodeInterface::STATUS_MULTIPLE_CHOICES
        ) {
            throw ServerException::with($request, $response);
        }

        if ($this->fetches === 0) {
            $this->streamHandler = $this->streamHandlerFactory->createStreamHandler($response->getBody());
        } else {
            $this->streamHandler->appendStream($response->getBody());
        }
        $this->fetches++;
    }

    /**
     * Fetches next result from server and returns all current loaded results. Null if cursor end has reached.
     *
     * @return string|array|object|null Data
     * @throws ClientExceptionInterface
     */
    public function fetch()
    {
        if (null === $this->streamHandler || $this->streamHandler->hasMore()) {
            $this->fetchOutstanding();
            return $this->streamHandler->result();
        }
        return null;
    }

    /**
     * Fetches all results from server and returns overall result.
     * This might issue additional HTTP requests to fetch any outstanding results from the server.
     *
     * @return string|array|object Data
     * @throws ClientExceptionInterface
     */
    public function fetchAll()
    {
        while (null === $this->streamHandler || $this->streamHandler->hasMore()) {
            $this->fetchOutstanding();
        }

        return $this->streamHandler->completeResult();
    }

    public function resultCount(): ?int
    {
        if (null === $this->streamHandler) {
            $this->fetchOutstanding();
        }
        return $this->streamHandler->resultCount();
    }

    public function result()
    {
        if (null === $this->streamHandler) {
            $this->fetchOutstanding();
        }
        return $this->streamHandler->result();
    }

    /**
     * Get the total number of results in the cursor.
     *
     * This might issue additional HTTP requests to fetch any outstanding results from the server.
     *
     * @return int Total number of results
     * @throws ClientExceptionInterface
     */
    public function count()
    {
        if (null === $this->streamHandler) {
            $this->fetchOutstanding();
        }

        while ($this->streamHandler->hasMore()) {
            $this->fetchOutstanding();
        }

        return $this->streamHandler->count();
    }

    /**
     * Rewind the cursor, loads first batch, can be repeated (new cursor will be created)
     *
     * @return void
     * @throws ClientExceptionInterface
     */
    public function rewind()
    {
        $this->fetches = 0;
        $this->fetchOutstanding();
    }

    /**
     * Return the current result row depending on stream handler
     *
     * @return string|array|object Data
     */
    public function current()
    {
        if (null === $this->streamHandler) {
            $this->fetchOutstanding();
        }
        return $this->streamHandler->current();
    }

    public function key(): int
    {
        if (null === $this->streamHandler) {
            $this->fetchOutstanding();
        }
        return $this->streamHandler->key();
    }

    public function next(): void
    {
        $this->streamHandler->next();
    }

    /**
     * @return bool
     * @throws ClientExceptionInterface
     */
    public function valid(): bool
    {
        if (null === $this->streamHandler) {
            $this->fetchOutstanding();
        }

        if (true === $this->streamHandler->valid()) {
            return true;
        }

        if (! $this->streamHandler->hasMore() || $this->streamHandler->cursorId() === null) {
            return false;
        }

        // need to fetch additional results from the server
        $this->fetchOutstanding();

        return $this->streamHandler->valid();
    }

    /**
     * Returns the number of HTTP calls that were made to build the cursor result
     *
     * @return int
     */
    public function fetches(): int
    {
        return $this->fetches;
    }

    public function cursorId(): ?string
    {
        if (null === $this->streamHandler) {
            $this->fetchOutstanding();
        }
        return $this->streamHandler->cursorId();
    }

    public function hasMore(): bool
    {
        if (null === $this->streamHandler) {
            $this->fetchOutstanding();
        }
        return $this->streamHandler->hasMore();
    }

    public function warnings(): array
    {
        if (null === $this->streamHandler) {
            $this->fetchOutstanding();
        }
        return $this->streamHandler->warnings();
    }

    public function fullCount(): ?int
    {
        if (null === $this->streamHandler) {
            $this->fetchOutstanding();
        }
        return $this->streamHandler->fullCount();
    }

    public function isCached(): bool
    {
        if (null === $this->streamHandler) {
            $this->fetchOutstanding();
        }
        return $this->streamHandler->isCached();
    }

    public function writesExecuted(): ?int
    {
        if (null === $this->streamHandler) {
            $this->fetchOutstanding();
        }
        return $this->streamHandler->writesExecuted();
    }

    public function writesIgnored(): ?int
    {
        if (null === $this->streamHandler) {
            $this->fetchOutstanding();
        }
        return $this->streamHandler->writesIgnored();
    }

    public function scannedFull(): ?int
    {
        if (null === $this->streamHandler) {
            $this->fetchOutstanding();
        }
        return $this->streamHandler->scannedFull();
    }

    public function scannedIndex(): ?int
    {
        if (null === $this->streamHandler) {
            $this->fetchOutstanding();
        }
        return $this->streamHandler->scannedIndex();
    }

    public function filtered(): ?int
    {
        if (null === $this->streamHandler) {
            $this->fetchOutstanding();
        }
        return $this->streamHandler->filtered();
    }

    /**
     * @return string|array|object Complete response body data
     */
    public function raw()
    {
        return $this->streamHandler->raw();
    }
}
