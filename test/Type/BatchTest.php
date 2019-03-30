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

use ArangoDb\Type\Batch;
use ArangoDb\Type\Collection;
use ArangoDb\Type\Document;
use ArangoDbTest\TestCase;
use Fig\Http\Message\StatusCodeInterface;

class BatchTest extends TestCase
{
    /**
     * @test
     */
    public function it_handles_types(): void
    {
        $types = [
            Collection::create('xyz'),
            Document::create('xyz', ['a' => 1, 'b' => 2, 'c' => 3]),
            Document::create('xyz', ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4]),
            Document::create('xyz', ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5]),
        ];

        $batch = Batch::fromTypes(...$types);

        $response = $this->client->sendRequest(
            $batch->toRequest($this->requestFactory, $this->streamFactory)
        );

        $this->assertEquals(
            StatusCodeInterface::STATUS_OK,
            $response->getStatusCode(),
            $response->getBody()->getContents()
        );
    }
}
