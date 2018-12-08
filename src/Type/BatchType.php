<?php
/**
 * Sandro Keil (https://sandro-keil.de)
 *
 * @link      http://github.com/sandrokeil/arangodb-php-client for the canonical source repository
 * @copyright Copyright (c) 2018 Sandro Keil
 * @license   http://github.com/sandrokeil/arangodb-php-client/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ArangoDb\Type;

use ArangoDb\Guard\Guard;

interface BatchType extends Type
{
    /**
     * Boundary string for batch request parts
     */
    public const MIME_BOUNDARY = 'AAApartAAA';

    /**
     * End of line mark used in HTTP
     */
    public const EOL = "\r\n";

    /**
     * Separator between header and body
     */
    public const BODY_SEPARATOR = "\r\n\r\n";

    /**
     * @see https://docs.arangodb.com/3.4/HTTP/BatchRequest/
     *
     * @param Type ...$types
     * @return BatchType
     */
    public static function fromTypes(Type ...$types): self;

    /**
     * @return Guard[]|null
     */
    public function guards(): ?array;
}
