<?php
/**
 * Sandro Keil (https://sandro-keil.de)
 *
 * @link      http://github.com/sandrokeil/arangodb-php-client for the canonical source repository
 * @copyright Copyright (c) 2018 Sandro Keil
 * @license   http://github.com/sandrokeil/arangodb-php-client/blob/master/LICENSE.md New BSD License
 */
declare(strict_types=1);

namespace ArangoDb\Exception;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Client\RequestExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Throwable;

final class RequestFailedException extends RuntimeException implements RequestExceptionInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    public static function ofRequest(RequestInterface $request, Throwable $previousException = null): self
    {
        $self = new self(
            sprintf('Request to "%s" failed.', $request->getUri()),
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
