<?php
/**
 * Sandro Keil (https://sandro-keil.de)
 *
 * @link      http://github.com/sandrokeil/arangodb-php-client for the canonical source repository
 * @copyright Copyright (c) 2018-2020 Sandro Keil
 * @license   http://github.com/sandrokeil/arangodb-php-client/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ArangoDbTest\Http;

use ArangoDb\Type\Collection;
use ArangoDb\Type\Document;
use ArangoDb\Http\Url;
use ArangoDbTest\TestCase;
use ArangoDbTest\TestUtil;
use Fig\Http\Message\RequestMethodInterface;
use Fig\Http\Message\StatusCodeInterface;

class ClientTest extends TestCase
{
    /**
     * @test
     */
    public function it_supports_head_requests(): void
    {
        $createCollection = Collection::create(__FUNCTION__);
        $response = $this->client->sendRequest(
            $createCollection->toRequest(
                $this->requestFactory,
                $this->streamFactory
            )
        );

        $this->assertEquals(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

        $response = $this->client->sendRequest(
            Document::create(__FUNCTION__, ['_key' => 'a123'])->toRequest($this->requestFactory, $this->streamFactory)
        );
        $this->assertEquals(StatusCodeInterface::STATUS_ACCEPTED, $response->getStatusCode());

        $response = $this->client->sendRequest(
            $this->requestFactory->createRequest(
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
        $response = $this->client->sendRequest(
            $createCollection->toRequest($this->requestFactory, $this->streamFactory)
        );

        $this->assertEquals(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

        $data = TestUtil::getResponseContent($response);

        $this->assertSame(200, $data['code']);
    }
}
