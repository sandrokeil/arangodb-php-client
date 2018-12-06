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

use ArangoDb\Type\GuardSupport;

final class GuardContentIdCollisionException extends RuntimeException
{
    /**
     * Type
     *
     * @var GuardSupport
     */
    private $type;

    public static function withType(GuardSupport $type): self
    {
        $self = new self(
            sprintf('Content id "%s" is already in use.', $type->guard()->contentId())
        );
        $self->type = $type;
        return $self;
    }

    /**
     * @return GuardSupport
     */
    public function type(): GuardSupport
    {
        return $this->type;
    }
}
