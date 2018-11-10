<?php
/**
 * Sandro Keil (https://sandro-keil.de)
 *
 * @link      http://github.com/sandrokeil/arangodb-php-client for the canonical source repository
 * @copyright Copyright (c) 2018 Sandro Keil
 * @license   http://github.com/sandrokeil/arangodb-php-client/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ArangoDbTest;


use ArangoDb\Client;

class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Client
     */
    protected $client;

    public static function setUpBeforeClass()
    {
        TestUtil::createDatabase();
    }

    public static function tearDownAfterClass()
    {
        TestUtil::dropDatabase();
    }

    protected function setUp()
    {
        $this->client = TestUtil::getClient();
    }
}