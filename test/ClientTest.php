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
use ArangoDb\Type\CreateCollection;
use ArangoDb\Type\CreateDatabase;
use ArangoDb\Type\DeleteDatabase;
use Fig\Http\Message\StatusCodeInterface;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    /**
     * @var Client
     */
    private $client;

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
        $this->client = TestUtil::getClient(class_exists('Velocypack\Vpack'));
    }

    /**
     * @test
     */
    public function it_creates_collection(): void
    {
        $createCollection = CreateCollection::with('myCol');
        $response = $this->client->sendRequest($createCollection->toRequest());

        $this->assertEquals(StatusCodeInterface::STATUS_OK, $response->getStatusCode(), $response->getBody()->getContents());
    }
}