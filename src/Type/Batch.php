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

use ArangoDb\Exception\GuardContentIdCollisionException;
use ArangoDb\Guard\Guard;
use ArangoDb\Http\Request;
use ArangoDb\Url;
use Fig\Http\Message\RequestMethodInterface;
use Psr\Http\Message\RequestInterface;

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
            if ($type instanceof GuardSupport && $guard = $type->guard()) {
                if ($contentId = $guard->contentId()) {
                    $key = $guard->contentId();
                }
                $this->guards[] = $guard;
            }
            if (isset($this->types[$key])) {
                throw GuardContentIdCollisionException::withType($type);
            }
            $this->types[$key] = $type;
        }
    }

    public static function fromTypes(Type ...$types): BatchType
    {
        return new self(...$types);
    }

    public function toRequest(): RequestInterface
    {
        $body = '';

        $boundary = '--' . self::MIME_BOUNDARY . self::EOL;
        $boundary .= 'Content-Type: application/x-arango-batchpart' . self::EOL;

        foreach ($this->types as $key => $type) {
            $body .= $boundary;
            $body .= 'Content-Id: ' . $key . self::BODY_SEPARATOR;

            $body .= $this->typeToString($type) . self::EOL;
        }
        $body .= '--' . self::MIME_BOUNDARY . '--' . self::BODY_SEPARATOR;

        $request = new Request(
            RequestMethodInterface::METHOD_POST,
            Url::BATCH,
            [
                'Content-Type' => 'multipart/form-data',
                'boundary' => self::MIME_BOUNDARY,
            ]
        );

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
     * @return string
     */
    private function typeToString(Type $type): string
    {
        $request = $type->toRequest();
        $body = $request->getBody()->getContents();

        if ($body) {
            $body = self::EOL . self::EOL . $body;
        }

        $body = $request->getMethod() . ' '
            . $request->getUri()->__toString()
            . ' HTTP/' . $request->getProtocolVersion()
            . $body;

        return $body;
    }
}
