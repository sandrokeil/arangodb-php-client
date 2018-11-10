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

use ArangoDb\Type\Collection;
use ArangoDb\Type\Index;
use ArangoDb\Type\Document;
use ArangoDb\Http\VpackStream;
use ArangoDb\Url;
use Fig\Http\Message\RequestMethodInterface;
use Fig\Http\Message\StatusCodeInterface;
use ArangoDb\Http\Request;

class ClientTest extends TestCase
{
    /**
     * @test
     */
    public function it_supports_head_requests(): void
    {
        $createCollection = Collection::create(__FUNCTION__);
        $response = $this->client->sendRequest($createCollection->toRequest());

        $this->assertEquals(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

        $response = $this->client->sendRequest(Document::create(__FUNCTION__, ['_key' => 'a123'])->toRequest());
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
        $createCollection = Collection::create('myCol');
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


}