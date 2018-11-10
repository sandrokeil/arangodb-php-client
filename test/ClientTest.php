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
use ArangoDb\Type\CountCollection;
use ArangoDb\Type\CreateCollection;
use ArangoDb\Type\CreateIndex;
use ArangoDb\Type\InsertDocument;
use ArangoDb\Http\VpackStream;
use ArangoDb\Url;
use Fig\Http\Message\RequestMethodInterface;
use Fig\Http\Message\StatusCodeInterface;
use ArangoDb\Http\Request;
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
        $this->client = TestUtil::getClient();
    }

    /**
     * @test
     */
    public function it_supports_head_requests(): void
    {
        $createCollection = CreateCollection::with(__FUNCTION__);
        $response = $this->client->sendRequest($createCollection->toRequest());

        $this->assertEquals(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

        $response = $this->client->sendRequest(InsertDocument::with(__FUNCTION__, ['_key' => 'a123'])->toRequest());
        $this->assertEquals(StatusCodeInterface::STATUS_ACCEPTED, $response->getStatusCode());

        $response = $this->client->sendRequest(
            new Request(
                RequestMethodInterface::METHOD_HEAD,
                Url::DOCUMENT .'/' . __FUNCTION__ . '/a123'
            )
        );
        $this->assertEquals('', $response->getBody()->getContents());
        $this->assertEquals(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function it_creates_collection(): void
    {
        $createCollection = CreateCollection::with('myCol');
        $response = $this->client->sendRequest($createCollection->toRequest());

        $this->assertEquals(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

        $body = $response->getBody();

        if ($body instanceof VpackStream) {
            $content = $body->vpack()->toJson();
            $this->assertEquals($content, $body->getContents());
        } else {
            $content = $body->getContents();
        }

        $this->assertStringStartsWith('{"code":200,', $content);
    }

    /**
     * @test
     */
    public function it_creates_collection_index(): void
    {
        $createCollection = CreateCollection::with('myColIndex');
        $response = $this->client->sendRequest($createCollection->toRequest());

        $this->assertEquals(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

        $body = $response->getBody();

        if ($body instanceof VpackStream) {
            $content = $body->vpack()->toJson();
            $this->assertEquals($content, $body->getContents());
        } else {
            $content = $body->getContents();
        }

        $this->assertStringStartsWith('{"code":200,', $content);

        $createCollection = CreateIndex::with(
            'myColIndex',
            [
                'type' => 'hash',
                'fields' => [
                    'real_stream_name',
                ],
                'selectivityEstimate' => 1,
                'unique' => true,
                'sparse' => false,
            ]
        );
        $response = $this->client->sendRequest($createCollection->toRequest());

        $this->assertEquals(StatusCodeInterface::STATUS_CREATED, $response->getStatusCode());

        $body = $response->getBody();

        if ($body instanceof VpackStream) {
            $content = $body->vpack()->toJson();
            $this->assertEquals($content, $body->getContents());
        } else {
            $content = $body->getContents();
        }

        $this->assertStringStartsWith('{"code":201', $content);
    }

    /**
     * @test
     */
    public function it_inserts_document(): void
    {
        $createCollection = CreateCollection::with(__FUNCTION__);
        $response = $this->client->sendRequest($createCollection->toRequest());

        $this->assertEquals(StatusCodeInterface::STATUS_OK, $response->getStatusCode());


        $response = $this->client->sendRequest(InsertDocument::with(__FUNCTION__, ['test' => 'valid'])->toRequest());
        $this->assertEquals(StatusCodeInterface::STATUS_ACCEPTED, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function it_inserts_documents(): void
    {
        $createCollection = CreateCollection::with(__FUNCTION__);
        $response = $this->client->sendRequest($createCollection->toRequest());

        $this->assertEquals(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

        $documents = InsertDocument::with(
            __FUNCTION__, [
                ['test' => 'valid'],
                ['test2' => 'valid2'],
                ['test3' => 'valid3'],
            ]
        );

        $response = $this->client->sendRequest($documents->toRequest());
        $this->assertEquals(StatusCodeInterface::STATUS_ACCEPTED, $response->getStatusCode());

        $response = $this->client->sendRequest(CountCollection::with(__FUNCTION__)->toRequest());
        $this->assertEquals(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

        $body = $response->getBody();

        if ($body instanceof VpackStream) {
            $count = $body->vpack()->access('count');
        } else {
            $count = json_decode($body->getContents(), true)['count'];
        }

        $this->assertEquals(3, $count);
    }

}