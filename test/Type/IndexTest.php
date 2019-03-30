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
use ArangoDb\Type\Index;
use ArangoDbTest\TestCase;
use ArangoDbTest\TestUtil;
use Fig\Http\Message\StatusCodeInterface;

class IndexTest extends TestCase
{
    private const COLLECTION_NAME = 'myColIndex';

    /**
     * @test
     */
    public function it_creates_collection_index(): void
    {
        $createCollection = Collection::create(self::COLLECTION_NAME);
        $response = $this->client->sendRequest($createCollection->toRequest($this->requestFactory, $this->streamFactory));

        $this->assertEquals(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

        $content = TestUtil::getResponseContent($response);

        $this->assertNotFalse(strpos($content, '"code":200'));

        $createCollection = Index::create(
            self::COLLECTION_NAME,
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
        $response = $this->client->sendRequest($createCollection->toRequest($this->requestFactory, $this->streamFactory));

        $this->assertEquals(StatusCodeInterface::STATUS_CREATED, $response->getStatusCode());

        $content = TestUtil::getResponseContent($response);

        $this->assertNotFalse(strpos($content, '"code":201'));
    }

    /**
     * @test
     * @depends it_creates_collection_index
     */
    public function it_reads_all_indexes(): string
    {
        $response = $this->client->sendRequest(Index::listAll(self::COLLECTION_NAME)->toRequest($this->requestFactory, $this->streamFactory));

        $this->assertEquals(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

        $content = TestUtil::getResponseContent($response);
        $data = json_decode($content, true);

        return $data['indexes'][1]['id'] ?? '';
    }

    /**
     * @test
     * @depends it_reads_all_indexes
     */
    public function it_reads_index(string $indexName): string
    {
        $response = $this->client->sendRequest(Index::info($indexName)->toRequest($this->requestFactory, $this->streamFactory));

        $this->assertEquals(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

        $content = TestUtil::getResponseContent($response);
        $this->assertNotFalse(strpos($content, '"code":200'));
        return $indexName;
    }

    /**
     * @test
     * @depends it_reads_index
     */
    public function it_deletes_index(string $indexName): void
    {
        $response = $this->client->sendRequest(Index::delete($indexName)->toRequest($this->requestFactory, $this->streamFactory));

        $this->assertEquals(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

        $content = TestUtil::getResponseContent($response);

        $this->assertNotFalse(strpos($content, '"code":200'));
    }
}
