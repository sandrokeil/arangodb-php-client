<?php
/**
 * Sandro Keil (https://sandro-keil.de)
 *
 * @link      http://github.com/sandrokeil/arangodb-php-client for the canonical source repository
 * @copyright Copyright (c) 2018-2019 Sandro Keil
 * @license   http://github.com/sandrokeil/arangodb-php-client/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ArangoDb\Type;

use ArangoDb\Url;
use ArangoDb\Util\Json;
use Fig\Http\Message\RequestMethodInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

final class Cursor implements CursorType
{
    /**
     * @var array
     */
    private $options;

    /**
     * @var string
     */
    private $uri;

    /**
     * @var string
     */
    private $method;

    private function __construct(
        string $uri,
        string $method,
        array $options = []
    ) {
        $this->uri = $uri;
        $this->method = $method;
        $this->options = $options;
    }

    public static function create(
        string $query,
        array $bindVars = [],
        int $batchSize = null,
        bool $count = false,
        bool $cache = null,
        array $options = []
    ): CursorType {
        $params = [
            'query' => $query,
            'count' => $count,
        ];

        if ($batchSize !== null) {
            $params['batchSize'] = $batchSize;
        }

        if (0 !== count($bindVars)) {
            $params['bindVars'] = $bindVars;
        }

        if (0 !== count($options)) {
            $params['options'] = $options;
        }

        if ($cache !== null) {
            $params['cache'] = $cache;
        }

        return new self(
            '',
            RequestMethodInterface::METHOD_POST,
            $params
        );
    }

    public static function delete(string $cursorId): CursorType
    {
        return new self(
            '/' . $cursorId,
            RequestMethodInterface::METHOD_DELETE
        );
    }

    public static function nextBatch(string $cursorId): CursorType
    {
        return new self(
            '/' . $cursorId,
            RequestMethodInterface::METHOD_PUT
        );
    }

    public function toRequest(
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory
    ): RequestInterface {
        $request = $requestFactory->createRequest($this->method, Url::CURSOR . $this->uri);

        if (0 === count($this->options)) {
            return $request;
        }

        return $request->withBody($streamFactory->createStream(Json::encode($this->options)));
    }
}
