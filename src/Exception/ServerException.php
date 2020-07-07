<?php
/**
 * Sandro Keil (https://sandro-keil.de)
 *
 * @link      http://github.com/sandrokeil/arangodb-php-client for the canonical source repository
 * @copyright Copyright (c) 2018-2020 Sandro Keil
 * @license   http://github.com/sandrokeil/arangodb-php-client/blob/master/LICENSE.md New BSD License
 */

namespace ArangoDb\Exception;

use ArangoDb\Type\Type;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\ResponseInterface;

final class ServerException extends RuntimeException implements ClientExceptionInterface
{
    /**
     * @var ResponseInterface
     */
    private $response;

    /**
     * @var Type
     */
    private $type;

    public static function with(Type $type, ResponseInterface $response): self
    {
        $self = new self(
            sprintf('Response with status code "%s" was returned.', $response->getStatusCode()),
            $response->getStatusCode()
        );
        $self->type = $type;
        $self->response = $response;
        return $self;
    }

    public function getType(): Type
    {
        return $this->type;
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }
}
