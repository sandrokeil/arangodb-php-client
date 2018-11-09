<?php
/**
 * Sandro Keil (https://sandro-keil.de)
 *
 * @link      http://github.com/sandrokeil/arangodb-php-client for the canonical source repository
 * @copyright Copyright (c) 2018 Sandro Keil
 * @license   http://github.com/sandrokeil/arangodb-php-client/blob/master/LICENSE.md New BSD License
 */

namespace ArangoDb\Exception;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Throwable;

final class NetworkException extends RuntimeException implements ClientExceptionInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    public static function for(RequestInterface $request, Throwable $previousException = null): self
    {
        $self = new self(
            'Response could no be evaluated.',
            StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR,
            $previousException
        );
        $self->request = $request;
        return $self;
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }
}
