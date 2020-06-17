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

use ArangoDb\Type\Type;
use Psr\Http\Message\ResponseInterface;

class UnexpectedResponse extends RuntimeException
{
    /**
     * @var Type
     */
    private $type;

    /**
     * @var ResponseInterface
     */
    private $response;

    public static function forType(Type $type, ResponseInterface $response): self
    {
        $self = new self('Unexpected response data.');
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
