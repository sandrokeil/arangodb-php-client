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

use ArangoDb\BatchResult;
use ArangoDb\Exception\InvalidArgumentException;
use ArangoDb\Exception\LogicException;
use ArangoDb\Guard\Guard;
use ArangoDb\Type\Batch;
use ArangoDb\Type\Collection;
use ArangoDb\Type\Document;
use ArangoDbTest\TestCase;
use ArangoDbTest\TestUtil;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;

class BatchResultTest extends TestCase
{
    private const COLLECTION_NAME = 'xyz';

    protected function setUp(): void
    {
        parent::setUp();
        $this->streamFactory = TestUtil::getStreamFactory(true);
    }

    /**
     * @test
     */
    public function it_throws_exception_if_not_multipart(): void
    {
        $response = $this->responseFactory->createResponse();
        $response->withHeader('Content-type', 'application/json');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Provided $batchResponse');

        BatchResult::fromResponse($response, $this->responseFactory, $this->streamFactory);
    }

    /**
     * @test
     */
    public function it_throws_exception_if_should_validate_with_no_guards(): void
    {
        $create = Collection::create(self::COLLECTION_NAME);

        $types = [
            $create
        ];

        $batch = Batch::fromTypes(...$types);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('No guards are provided');

        $response = $this->client->sendRequest(
            $batch->toRequest($this->requestFactory, $this->streamFactory)
        );
        $batchResult = BatchResult::fromResponse($response, $this->responseFactory, $this->streamFactory);
        $batchResult->validateBatch($batch);
    }

    /**
     * @test
     */
    public function it_supports_null_guard(): void
    {
        $guard = new class () implements Guard {
            public $counter = 0;

            public function __invoke(ResponseInterface $response): void
            {
                $this->counter++;
            }

            public function contentId(): ?string
            {
                return null;
            }
        };
        $create = Collection::create(self::COLLECTION_NAME);
        $create->useGuard($guard);

        $types = [
            $create,
            Document::create(self::COLLECTION_NAME, ['a' => 1, 'b' => 2, 'c' => 3]),
            Document::create(self::COLLECTION_NAME, ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4]),
            Document::create(self::COLLECTION_NAME, ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5]),
        ];

        $batch = Batch::fromTypes(...$types);

        $response = $this->client->sendRequest(
            $batch->toRequest($this->requestFactory, $this->streamFactory)
        );
        $batchResult = BatchResult::fromResponse($response, $this->responseFactory, $this->streamFactory);
        $batchResult->validate(...$batch->guards());
        $this->assertSame(4, $guard->counter);
    }

    /**
     * @test
     */
    public function it_can_be_created_from_batch_response(): void
    {
        $guard = new class () implements Guard {
            public $counter = 0;
            public $name;

            public function __invoke(ResponseInterface $response): void
            {
                $response->getBody()->rewind();
                $data = json_decode($response->getBody()->getContents());
                $this->name = $data->name;
                $this->counter++;
            }

            public function contentId(): ?string
            {
                return 'test';
            }
        };

        $create = Collection::create(self::COLLECTION_NAME);
        $create->useGuard($guard);

        $types = [
            $create,
            Document::create(self::COLLECTION_NAME, ['a' => 1, 'b' => 2, 'c' => 3]),
            Document::create(self::COLLECTION_NAME, ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4]),
            Document::create(self::COLLECTION_NAME, ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5]),
        ];

        $batch = Batch::fromTypes(...$types);

        $response = $this->client->sendRequest(
            $batch->toRequest($this->requestFactory, $this->streamFactory)
        );

        $this->assertEquals(
            StatusCodeInterface::STATUS_OK,
            $response->getStatusCode()
        );

        $batchResult = BatchResult::fromResponse($response, $this->responseFactory, $this->streamFactory);

        $this->assertCount(4, $batchResult);

        foreach ($batchResult as $response) {
            $data = TestUtil::getResponseContent($response, true);
            $this->assertNotNull($data);
            $this->assertTrue(is_array($data));
            $this->assertNotEmpty($data);
        }
        $batchResult->validate(...$batch->guards());
        $this->assertSame(1, $guard->counter);
        $this->assertSame(self::COLLECTION_NAME, $guard->name);
        $this->assertCount(4, $batchResult->responses());
    }

    protected function tearDown(): void
    {
        TestUtil::deleteCollection($this->client, self::COLLECTION_NAME);
    }
}
