<?php
/**
 * Sandro Keil (https://sandro-keil.de)
 *
 * @link      http://github.com/sandrokeil/arangodb-php-client for the canonical source repository
 * @copyright Copyright (c) 2018-2019 Sandro Keil
 * @license   http://github.com/sandrokeil/arangodb-php-client/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ArangoDb\Type;

use ArangoDb\Guard\Guard;
use ArangoDb\Url;
use Fig\Http\Message\RequestMethodInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

final class Batch implements BatchType
{
    /**
     * @var Type[]
     */
    private $types = [];

    /**
     * @var Guard[]
     */
    private $guards;

    public function __construct(Type ...$types)
    {
        foreach ($types as $key => $type) {
            if ($type instanceof GuardSupport && null !== ($guard = $type->guard())) {
                if (null !== $guard->contentId()) {
                    $key = $guard->contentId();
                }
                $this->guards[] = $guard;
            }
            $this->types[$key] = $type;
        }
    }

    public static function fromTypes(Type ...$types): BatchType
    {
        return new self(...$types);
    }

    public function toRequest(
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory
    ): RequestInterface {
        $body = '';

        $boundary = '--' . self::MIME_BOUNDARY . self::EOL;
        $boundary .= 'Content-Type: application/x-arango-batchpart' . self::EOL;

        foreach ($this->types as $key => $type) {
            $body .= $boundary;
            $body .= 'Content-Id: ' . $key . self::BODY_SEPARATOR;

            $body .= $this->typeToString($type, $requestFactory, $streamFactory) . self::EOL;
        }
        $body .= '--' . self::MIME_BOUNDARY . '--' . self::BODY_SEPARATOR;

        $request = $requestFactory->createRequest(RequestMethodInterface::METHOD_POST, Url::BATCH);
        $request = $request->withHeader('Content-Type', 'multipart/form-data');
        $request = $request->withHeader('boundary', self::MIME_BOUNDARY);

        $request->getBody()->write($body);
        $request->getBody()->rewind();

        return $request;
    }

    public function guards(): ?array
    {
        return $this->guards;
    }

    /**
     * Builds the batch request body
     *
     * @param Type $type
     * @param RequestFactoryInterface $requestFactory
     * @param StreamFactoryInterface $streamFactory
     * @return string
     */
    private function typeToString(
        Type $type,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory
    ): string {
        $request = $type->toRequest($requestFactory, $streamFactory);
        $body = $request->getBody()->getContents();

        if ('' !== $body) {
            $body = self::EOL . self::EOL . $body;
        }

        $body = $request->getMethod() . ' '
            . $request->getUri()->__toString()
            . ' HTTP/' . $request->getProtocolVersion()
            . $body;

        return $body;
    }
}
