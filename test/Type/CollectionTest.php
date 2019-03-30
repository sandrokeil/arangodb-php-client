<?php
/**
 * Sandro Keil (https://sandro-keil.de)
 *
 * @link      http://github.com/sandrokeil/arangodb-php-client for the canonical source repository
 * @copyright Copyright (c) 2018-2019 Sandro Keil
 * @license   http://github.com/sandrokeil/arangodb-php-client/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ArangoDbTest\Type;

use ArangoDb\Type\Collection;
use ArangoDbTest\TestCase;
use ArangoDbTest\TestUtil;
use Fig\Http\Message\StatusCodeInterface;

class CollectionTest extends TestCase
{
    private const COLLECTION_NAME = 'col';

    protected function tearDown(): void
    {
        TestUtil::deleteCollection($this->client, self::COLLECTION_NAME);
    }

    /**
     * @test
     */
    public function it_lists_all_collections(): void
    {
        $this->createTestCollection('col1');
        $this->createTestCollection('col2');

        $response = $this->client->sendRequest(
            Collection::listAll()->toRequest($this->requestFactory, $this->streamFactory)
        );
        $this->assertEquals(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

        $content = TestUtil::getResponseContent($response);
        $data = json_decode($content, true);
        $this->assertCount(2, $data['result'] ?? []);
    }

    /**
     * @test
     */
    public function it_counts_collection(): void
    {
        $this->createTestCollection(self::COLLECTION_NAME);

        $response = $this->client->sendRequest(
            Collection::count(self::COLLECTION_NAME)->toRequest($this->requestFactory, $this->streamFactory)
        );

        $content = TestUtil::getResponseContent($response);
        $data = json_decode($content, true);

        $this->assertEquals(0, $data['count'] ?? -1);
    }

    /**
     * @test
     */
    public function it_gets_collection_info(): void
    {
        $this->createTestCollection(self::COLLECTION_NAME);

        $response = $this->client->sendRequest(
            Collection::info(self::COLLECTION_NAME)->toRequest($this->requestFactory, $this->streamFactory)
        );

        $content = TestUtil::getResponseContent($response);
        $data = json_decode($content, true);

        $this->assertEquals(self::COLLECTION_NAME, $data['name'] ?? '');
    }

    private function createTestCollection(string $name): void
    {
        $response = $this->client->sendRequest(
            Collection::create($name)->toRequest($this->requestFactory, $this->streamFactory)
        );
        $this->assertEquals(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
    }
}
