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

use ArangoDb\Exception\LogicException;
use ArangoDBClient\Urls;
use Fig\Http\Message\RequestMethodInterface;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;

final class DeleteCursor implements Type
{
    /**
     * @var string
     */
    private $cursorId;

    private function __construct($cursorId)
    {
        $this->cursorId = $cursorId;
    }

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/AqlQueryCursor/AccessingCursors.html#delete-cursor
     *
     * @param string $cursorId
     * @return DeleteCursor
     */
    public static function with($cursorId): DeleteCursor
    {
        return new self($cursorId);
    }

    public function toRequest(): RequestInterface
    {
        return new Request(
            RequestMethodInterface::METHOD_DELETE,
            Urls::URL_CURSOR . '/' . $this->cursorId
        );
    }

    public function toJs(): string
    {
        throw new LogicException('Not possible at the moment, see ArangoDB docs');
    }
}
