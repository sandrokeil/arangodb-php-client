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

use ArangoDb\Exception\ConnectionException;
use ArangoDb\Exception\NetworkException;
use ArangoDb\Exception\RequestFailedException;
use ArangoDb\Exception\TimeoutException;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;

final class Client implements ClientInterface
{
    /**
     * Chunk size in bytes
     */
    private const CHUNK_SIZE = 8192;

    /**
     * End of line mark used in HTTP
     */
    private const EOL = "\r\n";

    /**
     * Connection handle
     *
     * @var resource
     */
    private $handle;

    /**
     * @var bool
     */
    private $useKeepAlive;

    /**
     * @var ClientOptions
     */
    private $options;

    /**
     * @var string
     */
    private $baseUrl = '';

    /**
     * Default headers for all requests
     *
     * @var string
     */
    private $headerLines = '';

    /**
     * @var string
     */
    private $database = '';

    /**
     * @var ResponseFactoryInterface
     */
    private $responseFactory;

    /**
     * @var StreamFactoryInterface
     */
    private $streamFactory;

    /**
     * @param array|ClientOptions $options
     * @param ResponseFactoryInterface $responseFactory
     * @param StreamFactoryInterface $streamFactory
     */
    public function __construct(
        $options,
        ResponseFactoryInterface $responseFactory,
        StreamFactoryInterface $streamFactory
    ) {
        $this->options = $options instanceof ClientOptions ? $options : new ClientOptions($options);
        $this->useKeepAlive = ($this->options[ClientOptions::OPTION_CONNECTION] === 'Keep-Alive');
        $this->responseFactory = $responseFactory;
        $this->streamFactory = $streamFactory;
        $this->updateCommonHttpHeaders();
    }

    /**
     * Delegate / shorthand method
     *
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws \Psr\Http\Client\ClientExceptionInterface
     */
    public function __invoke(RequestInterface $request): ResponseInterface
    {
        return $this->sendRequest($request);
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        try {
            $stream = $request->getBody();
            $body = $stream->getContents();
            $method = $request->getMethod();

            $customHeaders = $request->getHeaders();
            unset($customHeaders['Connection'], $customHeaders['Content-Length']);

            if (! isset($customHeaders['Content-Type'])) {
                $customHeaders['Content-Type'] = ['application/json'];
            }

            $customHeader = '';
            foreach ($customHeaders as $headerKey => $headerValues) {
                foreach ($headerValues as $headerValue) {
                    $customHeader .= $headerKey . ': ' . $headerValue . self::EOL;
                }
            }
        } catch (\Throwable $e) {
            throw RequestFailedException::ofRequest($request, $e);
        }

        $customHeader .= 'Content-Length: ' . $stream->getSize() . self::EOL;

        $url = $this->baseUrl . $request->getUri();

        try {
            $this->open($request);

            $result = $this->transmit(
                $method . ' ' . $url . ' HTTP/1.1' .
                $this->headerLines .
                $customHeader . self::EOL .
                $body,
                $method
            );
            // TODO https://docs.arangodb.com/3.4/Manual/Architecture/DeploymentModes/ActiveFailover/Architecture.html
            $status = stream_get_meta_data($this->handle);

            if (true === $status['timed_out']) {
                throw TimeoutException::ofRequest($request);
            }
            if (! $this->useKeepAlive) {
                $this->close();
            }

            [$httpCode, $headers, $body] = HttpHelper::parseMessage($result);
        } catch (\Throwable $e) {
            throw NetworkException::with($request, $e);
        }
        $response = $this->responseFactory->createResponse($httpCode);

        foreach ($headers as $headerName => $header) {
            $response = $response->withAddedHeader($headerName, $header);
        }

        return $response->withBody($this->streamFactory->createStream($body));
    }

    /**
     * Sends request to server and reads response.
     *
     * @param string $request
     * @param string $method
     * @return string
     */
    private function transmit(string $request, string $method): string
    {
        fwrite($this->handle, $request);
        fflush($this->handle);

        $contentLength = 0;
        $bodyLength = 0;
        $readTotal = 0;
        $matches = [];
        $message = '';

        do {
            $read = fread($this->handle, self::CHUNK_SIZE);
            if (false === $read || $read === '') {
                break;
            }
            $readLength = strlen($read);
            $readTotal += $readLength;
            $message .= $read;

            if ($contentLength === 0
                && $method !== 'HEAD'
                && 1 === preg_match('/content-length: (\d+)/i', $message, $matches)
            ) {
                $contentLength = (int)$matches[1];
            }

            if ($bodyLength === 0) {
                $bodyStart = strpos($message, "\r\n\r\n");

                if (false !== $bodyStart) {
                    $bodyLength = $bodyStart + $contentLength + 4;
                }
            }
        } while ($readTotal < $bodyLength && ! feof($this->handle));

        return $message;
    }

    /**
     * Update common HTTP headers for all HTTP requests
     */
    private function updateCommonHttpHeaders(): void
    {
        $this->headerLines = self::EOL;

        $endpoint = $this->options[ClientOptions::OPTION_ENDPOINT];
        if (1 !== preg_match('/^unix:\/\/.+/', $endpoint)) {
            $this->headerLines .= 'Host: '
                . preg_replace('/^(tcp|ssl):\/\/(.+?):(\d+)\/?$/', '\\2', $endpoint)
                . self::EOL;
        }
        // add basic auth header
        if (isset(
            $this->options[ClientOptions::OPTION_AUTH_TYPE],
            $this->options[ClientOptions::OPTION_AUTH_USER]
        )) {
            $this->headerLines .= sprintf(
                'Authorization: %s %s%s',
                $this->options[ClientOptions::OPTION_AUTH_TYPE],
                base64_encode(
                    $this->options[ClientOptions::OPTION_AUTH_USER] . ':' .
                    $this->options[ClientOptions::OPTION_AUTH_PASSWD]
                ),
                self::EOL
            );
        }

        if (isset($this->options[ClientOptions::OPTION_CONNECTION])) {
            $this->headerLines .= 'Connection: ' . $this->options[ClientOptions::OPTION_CONNECTION] . self::EOL;
        }

        $this->database = $this->options[ClientOptions::OPTION_DATABASE];
        $this->baseUrl = '/_db/' . urlencode($this->database);
    }

    /**
     * Opens connection depending on options.
     *
     * @param RequestInterface $request
     */
    private function open(RequestInterface $request): void
    {
        if ($this->useKeepAlive && $this->handle !== null && is_resource($this->handle)) {
            if (! feof($this->handle)) {
                return;
            }

            $this->close();

            if (false === $this->options[ClientOptions::OPTION_RECONNECT]) {
                throw ConnectionException::forRequest(
                    $request,
                    'Server has closed the connection already.',
                    StatusCodeInterface::STATUS_REQUEST_TIMEOUT
                );
            }
        }

        $endpoint = $this->options[ClientOptions::OPTION_ENDPOINT];
        $context = stream_context_create();

        if (1 === preg_match('/^ssl:\/\/.+/', $endpoint)) {
            stream_context_set_option(
                $context,
                [
                    'ssl' => [
                        'verify_peer' => $this->options[ClientOptions::OPTION_VERIFY_CERT],
                        'verify_peer_name' => $this->options[ClientOptions::OPTION_VERIFY_CERT_NAME],
                        'allow_self_signed' => $this->options[ClientOptions::OPTION_ALLOW_SELF_SIGNED],
                        'ciphers' => $this->options[ClientOptions::OPTION_CIPHERS],
                    ],
                ]
            );
        }

        $handle = stream_socket_client(
            $endpoint,
            $errNo,
            $message,
            $this->options[ClientOptions::OPTION_TIMEOUT],
            STREAM_CLIENT_CONNECT,
            $context
        );

        if (false === $handle) {
            throw ConnectionException::forRequest(
                $request,
                sprintf('Cannot connect to endpoint "%s". Message: %s', $endpoint, $message),
                $errNo
            );
        }
        $this->handle = $handle;
        stream_set_timeout($this->handle, $this->options[ClientOptions::OPTION_TIMEOUT]);
    }

    /**
     * Closes connection
     */
    private function close(): void
    {
        fclose($this->handle);
        unset($this->handle);
    }
}
