<?php
/**
 * Sandro Keil (https://sandro-keil.de)
 *
 * @link      http://github.com/sandrokeil/arangodb-php-client for the canonical source repository
 * @copyright Copyright (c) 2018-2019 Sandro Keil
 * @license   http://github.com/sandrokeil/arangodb-php-client/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ArangoDbTest;

use ArangoDb\Client;
use ArangoDb\Statement\StreamHandlerFactoryInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var ResponseFactoryInterface
     */
    protected $responseFactory;

    /**
     * @var RequestFactoryInterface
     */
    protected $requestFactory;

    /**
     * @var StreamFactoryInterface
     */
    protected $streamFactory;

    /**
     * @var StreamHandlerFactoryInterface
     */
    protected $streamHandlerFactory;

    public static function setUpBeforeClass(): void
    {
        TestUtil::createDatabase();
    }

    public static function tearDownAfterClass(): void
    {
        TestUtil::dropDatabase();
    }

    protected function setUp(): void
    {
        $this->client = TestUtil::getClient();
        $this->responseFactory = TestUtil::getResponseFactory();
        $this->requestFactory = TestUtil::getRequestFactory();
        $this->streamFactory = TestUtil::getStreamFactory();
        $this->streamHandlerFactory = TestUtil::getStreamHandlerFactory();
    }
}
