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

use ArangoDb\Guard\Guard;
use ArangoDb\Url;
use ArangoDb\Util\Json;
use Fig\Http\Message\RequestMethodInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;

final class Transaction implements TransactionType
{
    /**
     * @var bool
     */
    private $waitForSync;

    /**
     * @var array
     */
    private $collections;

    /**
     * @var array
     */
    private $params;

    /**
     * @var string
     */
    private $action;

    /**
     * Guard
     *
     * @var Guard
     */
    private $guard;

    private function __construct(
        string $action,
        array $collections,
        array $params,
        bool $waitForSync = false
    ) {
        $this->action = $action;
        $this->collections = $collections;
        $this->params = $params;
        $this->waitForSync = $waitForSync;
    }

    public static function with(
        string $action,
        array $write,
        array $params = [],
        array $read = [],
        bool $waitForSync = false
    ): TransactionType {
        return new self(
            $action,
            [
                'write' => $write,
                'read' => $read,
            ],
            $params,
            $waitForSync
        );
    }

    public function toRequest(
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory
    ): RequestInterface {
        $collections = $this->collections;

        if (isset($collections['read']) && 0 === count($collections['read'])) {
            unset($collections['read']);
        }
        $request = $requestFactory->createRequest(RequestMethodInterface::METHOD_POST, Url::TRANSACTION);


        return $request->withBody($streamFactory->createStream(Json::encode(
            [
                'action' => $this->action,
                'collections' => $collections,
                'params' => $this->params,
                'waitForSync' => $this->waitForSync,
            ]
        )));
    }

    public function useGuard(Guard $guard): Type
    {
        $this->guard = $guard;
        return $this;
    }

    public function guard(): ?Guard
    {
        return $this->guard;
    }
}
