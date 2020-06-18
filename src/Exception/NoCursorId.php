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

final class NoCursorId extends RuntimeException
{
    /**
     * @var Type
     */
    private $type;

    public static function forType(Type $type): self
    {
        $self = new self('There is no cursor id to get more results.');

        $self->type = $type;
        return $self;
    }

    public function getType(): Type
    {
        return $this->type;
    }
}
