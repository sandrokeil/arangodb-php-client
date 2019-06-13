<?php
/**
 * Sandro Keil (https://sandro-keil.de)
 *
 * @link      http://github.com/sandrokeil/arangodb-php-client for the canonical source repository
 * @copyright Copyright (c) 2018-2019 Sandro Keil
 * @license   http://github.com/sandrokeil/arangodb-php-client/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ArangoDb\Statement;

use Psr\Http\Message\StreamInterface;

class VpackStreamHandlerFactory implements StreamHandlerFactoryInterface
{
    /**
     * @var int
     */
    private $resultType;

    public function __construct(int $resultType)
    {
        $this->resultType = $resultType;
    }

    public function createStreamHandler(StreamInterface $body): StreamHandler
    {
        return new VpackStreamHandler($body, $this->resultType);
    }
}
