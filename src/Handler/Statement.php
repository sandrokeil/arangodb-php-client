<?php
/**
 * Sandro Keil (https://sandro-keil.de)
 *
 * @link      http://github.com/sandrokeil/arangodb-php-client for the canonical source repository
 * @copyright Copyright (c) 2018-2020 Sandro Keil
 * @license   http://github.com/sandrokeil/arangodb-php-client/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ArangoDb\Handler;

use ArangoDb\Http\TypeSupport;
use ArangoDb\Statement\Statement as StatementStatement;
use ArangoDb\Statement\StreamHandlerFactoryInterface;
use ArangoDb\Type\Cursor;

final class Statement implements StatementHandler
{
    /**
     * @var TypeSupport
     **/
    private $client;

    /**
     * @var StreamHandlerFactoryInterface
     */
    private $streamHandlerFactory;

    public function __construct(TypeSupport $client, StreamHandlerFactoryInterface $streamHandlerFactory)
    {
        $this->client = $client;
        $this->streamHandlerFactory = $streamHandlerFactory;
    }

    public function create(
        string $query,
        array $bindVars = [],
        int $batchSize = null,
        bool $count = false,
        bool $cache = null,
        array $options = []
    ): StatementStatement {
        return new StatementStatement(
            $this->client,
            Cursor::create(
                $query,
                $bindVars,
                $batchSize,
                $count,
                $cache,
                $options
            ),
            $this->streamHandlerFactory
        );
    }
}
