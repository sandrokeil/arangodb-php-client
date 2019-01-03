<?php
/**
 * Sandro Keil (https://sandro-keil.de)
 *
 * @link      http://github.com/sandrokeil/arangodb-php-client for the canonical source repository
 * @copyright Copyright (c) 2018-2019 Sandro Keil
 * @license   http://github.com/sandrokeil/arangodb-php-client/blob/master/LICENSE.md New BSD License
 */

namespace ArangoDb\Exception;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Message\RequestInterface;

final class TimeoutException extends RuntimeException implements NetworkExceptionInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    public static function ofRequest(RequestInterface $request): self
    {
        $self = new self(
            "Got a timeout while waiting for the server's response",
            StatusCodeInterface::STATUS_REQUEST_TIMEOUT
        );
        $self->request = $request;
        return $self;
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }
}
