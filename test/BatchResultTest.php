<?php
/**
 * Sandro Keil (https://sandro-keil.de)
 *
 * @link      http://github.com/sandrokeil/arangodb-php-client for the canonical source repository
 * @copyright Copyright (c) 2018 Sandro Keil
 * @license   http://github.com/sandrokeil/arangodb-php-client/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ArangoDbTest\Type;

use ArangoDb\BatchResult;
use ArangoDb\Exception\InvalidArgumentException;
use ArangoDb\Http\Response;
use ArangoDb\Type\Batch;
use ArangoDb\Type\Collection;
use ArangoDb\Type\Document;
use ArangoDbTest\TestCase;
use Fig\Http\Message\StatusCodeInterface;

class BatchResultTest extends TestCase
{
    /**
     * @test
     */
    public function it_throws_exception_if_not_multipart(): void
    {
        $response = new Response(StatusCodeInterface::STATUS_OK, ['Content-type' => 'application/json']);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Provided $batchResponse');

        BatchResult::fromResponse($response);
    }

    /**
     * @test
     */
    public function it_can_be_created_from_batch_response(): void
    {
        $types = [
            Collection::create('xyz'),
            Document::create('xyz', ['a' => 1, 'b' => 2, 'c' => 3]),
            Document::create('xyz', ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4]),
            Document::create('xyz', ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5]),
        ];

        $batch = Batch::fromTypes(...$types);

        $response = $this->client->sendRequest(
            $batch->toRequest()
        );

        $this->assertEquals(
            StatusCodeInterface::STATUS_OK,
            $response->getStatusCode()
        );

        $batchResult = BatchResult::fromResponse($response);

        $this->assertCount(4, $batchResult);

        foreach ($batchResult as $response) {
            $this->assertContains(
                $response->getStatusCode(), [StatusCodeInterface::STATUS_ACCEPTED, StatusCodeInterface::STATUS_OK]
            );
            $data = json_decode($response->getBody()->getContents(), true);
            $this->assertNotNull($data);
            $this->assertInternalType('array', $data);
            $this->assertNotEmpty($data);
        }
    }
}