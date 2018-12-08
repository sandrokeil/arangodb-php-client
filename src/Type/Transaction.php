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
use ArangoDb\Http\Request;
use ArangoDb\Http\VpackStream;
use ArangoDb\Url;
use Fig\Http\Message\RequestMethodInterface;
use Psr\Http\Message\RequestInterface;

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

    public function toRequest(): RequestInterface
    {
        $collections = $this->collections;

        if (empty($collections['read'])) {
            unset($collections['read']);
        }

        return new Request(
            RequestMethodInterface::METHOD_POST,
            Url::TRANSACTION,
            [],
            new VpackStream(
                [
                    'action' => $this->action,
                    'collections' => $collections,
                    'params' => $this->params,
                    'waitForSync' => $this->waitForSync,
                ]
            )
        );
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
