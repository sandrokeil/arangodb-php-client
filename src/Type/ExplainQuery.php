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

final class ExplainQuery implements Type
{
    /**
     * @var array
     */
    private $params;

    private function __construct(array $params, array $options = [])
    {
        if (! empty($options)) {
            $params['options'] = $options;
        }
        $this->params = $params;
    }

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/AqlQuery/#explain-an-aql-query
     *
     * @param string $query
     * @param array $bindVars
     * @param array $options
     * @return ExplainQuery
     */
    public static function with(
        string $query,
        array $bindVars = [],
        array $options = []
    ): ExplainQuery {
        return new self(
            [
                'query' => $query,
                'bindVars' => $bindVars,
            ],
            $options
        );
    }

    public function toRequest(): RequestInterface
    {
        return new Request(
            RequestMethodInterface::METHOD_POST,
            Urls::URL_EXPLAIN,
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
