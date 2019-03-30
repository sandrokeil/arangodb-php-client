<?php
/**
 * Sandro Keil (https://sandro-keil.de)
 *
 * @link      http://github.com/sandrokeil/arangodb-php-client for the canonical source repository
 * @copyright Copyright (c) 2018-2019 Sandro Keil
 * @license   http://github.com/sandrokeil/arangodb-php-client/blob/master/LICENSE.md New BSD License
 */

namespace ArangoDbTest;

use ArangoDb\Statement;
use ArangoDb\Type\Cursor;

class StatementTest extends TestCase
{
    /**
     * @test
     */
    public function it_returns_data_with_batch_size(): void
    {
        $count = 1000;

        $statement = new Statement(
            $this->client,
            Cursor::create(
                'FOR i IN 1..' . $count . ' RETURN {"_key": i+1}',
                [],
                $count
            )->toRequest($this->requestFactory, $this->streamFactory),
            $this->requestFactory,
            $this->streamHandlerFactory
        );
        $this->assertEquals(0, $statement->fetches());

        $data = [];
        $i = 0;
        foreach ($statement as $key => $item) {
            $this->assertEquals($i, $key);
            $data[] = $item;
            $i++;
        }

        $this->assertNotEmpty($data);
        $this->assertCount($count, $data);
        $this->assertEquals(1, $statement->fetches());
    }

    /**
     * @test
     */
    public function it_returns_all_data_with_batch_size(): void
    {
        $statement = new Statement(
            $this->client,
            Cursor::create(
                'FOR i IN 0..99 RETURN {"_key": i+1}',
                [],
                10
            )->toRequest($this->requestFactory, $this->streamFactory),
            $this->requestFactory,
            $this->streamHandlerFactory
        );
        $this->assertEquals(0, $statement->fetches());

        $data = [];
        $i = 0;
        foreach ($statement as $key => $item) {
            $this->assertEquals($i, $key);
            $data[] = $item;
            $i++;
        }

        $this->assertNotEmpty($data);
        $this->assertCount(100, $data);
        $this->assertEquals(10, $statement->fetches());
    }

    /**
     * @test
     */
    public function it_supports_rewind(): void
    {
        $statement = new Statement(
            $this->client,
            Cursor::create(
                'FOR i IN 0..99 RETURN {"_key": i+1}',
                [],
                10
            )->toRequest($this->requestFactory, $this->streamFactory),
            $this->requestFactory,
            $this->streamHandlerFactory
        );

        $data = [];
        $i = 0;
        foreach ($statement as $key => $item) {
            $this->assertEquals($i, $key);
            $data[] = $item;
            $i++;
        }

        $this->assertNotEmpty($data);
        $this->assertCount(100, $data);
        $this->assertEquals(10, $statement->fetches());

        $data = iterator_to_array($statement);
        $this->assertNotEmpty($data);
        $this->assertCount(100, $data);
        $this->assertEquals(10, $statement->fetches());

        $statement->rewind();
        $this->assertEquals(1, $statement->fetches());

        $data = $statement->fetchAll();
        $this->assertNotEmpty($data);
        $this->assertCount(100, $data);
        $this->assertEquals(10, $statement->fetches());
    }

    /**
     * @test
     */
    public function it_supports_single_object(): void
    {
        $statement = new Statement(
            $this->client,
            Cursor::create('RETURN {"_key": 1}')->toRequest($this->requestFactory, $this->streamFactory),
            $this->requestFactory,
            $this->streamHandlerFactory
        );

        $data = $statement->fetchAll();
        $this->assertNotEmpty($data);
        $this->assertEquals(1, $data[0]['_key']);
        $this->assertEquals(1, $statement->fetches());
    }

    /**
     * @test
     */
    public function it_supports_arbitrary_data(): void
    {
        $statement = new Statement(
            $this->client,
            Cursor::create('RETURN "test"')->toRequest($this->requestFactory, $this->streamFactory),
            $this->requestFactory,
            $this->streamHandlerFactory
        );

        $data = $statement->fetchAll();
        $this->assertNotEmpty($data);
        $this->assertEquals('test', $data[0]);
        $this->assertEquals(1, $statement->fetches());
    }

    /**
     * @test
     */
    public function it_supports_count(): void
    {
        $statement = new Statement(
            $this->client,
            Cursor::create(
                'FOR i IN 0..99  LIMIT 50 RETURN {"_key": i+1}',
                [],
                10,
                true,
                null,
                ['fullCount' => true]
            )->toRequest($this->requestFactory, $this->streamFactory),
            $this->requestFactory,
            $this->streamHandlerFactory
        );

        $this->assertEquals(100, $statement->fullCount());
        $this->assertEquals(50, $statement->resultCount());
        $this->assertEquals(1, $statement->fetches());
        $this->assertCount(10, $statement->result());
        $this->assertEquals(50, $statement->count());
        $this->assertEquals(5, $statement->fetches());
        $this->assertCount(10, $statement->result());
        $this->assertCount(50, $statement->fetchAll());
    }

    /**
     * @test
     */
    public function it_fetches_data(): void
    {
        $statement = new Statement(
            $this->client,
            Cursor::create(
                'FOR i IN 0..99  LIMIT 50 RETURN {"_key": i+1}',
                [],
                10
            )->toRequest($this->requestFactory, $this->streamFactory),
            $this->requestFactory,
            $this->streamHandlerFactory
        );

        $loop = 0;

        while ($data = $statement->fetch()) {
            ++$loop;
            $this->assertCount(10, $data);
        }
        $this->assertEquals(5, $loop);
        $this->assertEquals(5, $statement->fetches());
    }
}
