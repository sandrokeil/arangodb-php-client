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
use ArangoDb\VpackStream;
use ArangoDBClient\Urls;
use Fig\Http\Message\RequestMethodInterface;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;

final class CreateCursor implements Type
{
    /**
     * @var array
     */
    private $params;

    private function __construct(array $params, array $options = [])
    {
        if ($params['batchSize'] === null) {
            unset($params['batchSize']);
        }
        if ($params['cache'] === null) {
            unset($params['cache']);
        }
        if (empty($params['bindVars'])) {
            unset($params['bindVars']);
        }
        if (! empty($options)) {
            $params['options'] = $options;
        }
        $this->params = $params;
    }

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/AqlQueryCursor/AccessingCursors.html
     *
     * @param string $query
     * @param array $bindVars
     * @param int|null $batchSize
     * @param bool $count
     * @param bool|null $cache
     * @param array $options
     * @return CreateCursor
     */
    public static function with(
        string $query,
        array $bindVars = [],
        int $batchSize = null,
        $count = false,
        bool $cache = null,
        array $options = []
    ): CreateCursor {
        return new self(
            [
                'query' => $query,
                'bindVars' => $bindVars,
                'batchSize' => $batchSize,
                'count' => $count,
                'cache' => $cache,
            ],
            $options
        );
    }

    public function toRequest(): RequestInterface
    {
        return new Request(
            RequestMethodInterface::METHOD_POST,
            Urls::URL_CURSOR,
            [],
            new VpackStream($this->params)
        );
    }

    public function toJs(): string
    {
        throw new LogicException('Not possible at the moment, see ArangoDB docs');
    }

    public function query(): string
    {
        return $this->params['query'];
    }

    public function bindVars(): array
    {
        return $this->params['bindVars'];
    }
}
