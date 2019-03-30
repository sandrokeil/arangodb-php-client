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

use ArangoDb\Type\Cursor;
use ArangoDbTest\TestCase;
use ArangoDbTest\TestUtil;
use Fig\Http\Message\StatusCodeInterface;

class CursorTest extends TestCase
{
    /**
     * @test
     */
    public function it_creates_cursor(): string
    {
        $response = $this->client->sendRequest(
            Cursor::create(
                'FOR i IN 0..99 RETURN {"_key": i+1}',
                [],
                10
            )->toRequest($this->requestFactory, $this->streamFactory)
        );
        $this->assertEquals(StatusCodeInterface::STATUS_CREATED, $response->getStatusCode());

        $content = TestUtil::getResponseContent($response);
        $data = json_decode($content, true);
        $this->assertNotEmpty($data['id']);
        return $data['id'];
    }

    /**
     * @depends it_creates_cursor
     * @test
     */
    public function it_fetches_next_batch(string $cursorId): string
    {
        $response = $this->client->sendRequest(
            Cursor::nextBatch($cursorId)->toRequest($this->requestFactory, $this->streamFactory)
        );
        $this->assertEquals(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

        $content = TestUtil::getResponseContent($response);
        $data = json_decode($content, true);
        $this->assertTrue($data['hasMore'] ?? false);

        return $cursorId;
    }

    /**
     * @depends it_fetches_next_batch
     * @test
     */
    public function it_deletes_cursor(string $cursorId): void
    {
        $response = $this->client->sendRequest(
            Cursor::delete($cursorId)->toRequest($this->requestFactory, $this->streamFactory)
        );
        $this->assertEquals(StatusCodeInterface::STATUS_ACCEPTED, $response->getStatusCode());
    }
}
