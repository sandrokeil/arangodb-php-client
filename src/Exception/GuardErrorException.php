<?php
/**
 * Sandro Keil (https://sandro-keil.de)
 *
 * @link      http://github.com/sandrokeil/arangodb-php-client for the canonical source repository
 * @copyright Copyright (c) 2018-2020 Sandro Keil
 * @license   http://github.com/sandrokeil/arangodb-php-client/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ArangoDb\Exception;

use Psr\Http\Message\ResponseInterface;

class GuardErrorException extends RuntimeException
{
    /**
     * @var ResponseInterface
     */
    private $response;

    public static function with(ResponseInterface $response): self
    {
        $self = new self(
            'A guard has an error detected.',
            $response->getStatusCode()
        );
        $self->response = $response;
        return $self;
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }
}
