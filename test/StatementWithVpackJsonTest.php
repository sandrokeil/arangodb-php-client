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
use ArangoDb\Statement\VpackStreamHandler;
use ArangoDb\Statement\VpackStreamHandlerFactory;
use ArangoDb\Type\Cursor;

/**
 * @group vpack
 */
class StatementWithVpackJsonTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->streamHandlerFactory = new VpackStreamHandlerFactory(VpackStreamHandler::RESULT_TYPE_JSON);
    }

    /**
     * @test
     */
    public function it_returns_data_with_batch_size(): void
    {
        $count = 999;

        $statement = new Statement(
            $this->client,
            Cursor::create(
                'FOR i IN 0..' . $count . ' RETURN {"_key": i+1}',
                [],
                $count + 1
            )->toRequest($this->requestFactory, $this->streamFactory),
            $this->requestFactory,
            $this->streamHandlerFactory
        );
        $this->assertEquals(0, $statement->fetches());

        $data = '[';
        $i = 0;
        foreach ($statement as $key => $item) {
            $this->assertEquals($i, $key);
            $data .= $i === 0 ? $item : ',' . $item;
            $i++;
        }
        $data .= ']';

        $this->assertNotEmpty($data);

        $this->assertStringStartsWith(
            '[{"_key":1},{"_key":2},{"_key":3},{"_key":4},{"_key":5},{"_key":6},{"_key":7},{',
            $data
        );
        $this->assertStringEndsWith(
            '{"_key":998},{"_key":999},{"_key":1000}]',
            $data
        );

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

        $data = '[';
        $i = 0;
        foreach ($statement as $key => $item) {
            $this->assertEquals($i, $key);
            $data .= $i === 0 ? $item : ',' . $item;
            $i++;
        }
        $data .= ']';

        $this->assertData100($data);

        $this->assertEquals(10, $statement->fetches());
    }

    private function assertData100(string $data)
    {
        $this->assertNotEmpty($data);

        $this->assertStringStartsWith(
            '[{"_key":1},{"_key":2},{"_key":3},{"_key":4},{"_key":5},{"_key":6},{"_key":7},{',
            $data
        );
        $this->assertStringEndsWith(
            '{"_key":98},{"_key":99},{"_key":100}]',
            $data
        );
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

        $data = '[';
        $i = 0;
        foreach ($statement as $key => $item) {
            $this->assertEquals($i, $key);
            $data .= $i === 0 ? $item : ',' . $item;
            $i++;
        }
        $data .= ']';

        $this->assertData100($data);
        $this->assertEquals(10, $statement->fetches());

        $data = iterator_to_array($statement);
        $this->assertData100('[' . implode(',', $data) . ']');
        $this->assertEquals(10, $statement->fetches());

        $statement->rewind();
        $this->assertEquals(1, $statement->fetches());

        $data = $statement->fetchAll();
        $this->assertData100($data);
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
        $this->assertEquals('[{"_key":1}]', $data);
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
        $this->assertEquals('["test"]', $data);
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

        $this->assertStringStartsWith(
            '[{"_key":1},{"_key":2},{"_key":3},{"_key":4},{"_key":5},{"_key":6},{"_key":7},{"_key":8},{"_key":9},{"_key":10}]',
            $statement->result()
        );

        $this->assertEquals(50, $statement->count());
        $this->assertEquals(5, $statement->fetches());

        $this->assertStringStartsWith(
            '[{"_key":41},{"_key":42},{"_key":43},{"_key":44},{"_key":45},{"_key":46},{"_key":47},{"_key":48},{"_key":49},{"_key":50}]',
            $statement->result()
        );

        $data = $statement->fetchAll();

        $this->assertStringStartsWith(
            '[{"_key":1},{"_key":2},{"_key":3},{"_key":4},{"_key":5},{"_key":6},{"_key":7},{',
            $data
        );
        $this->assertStringEndsWith(
            '{"_key":48},{"_key":49},{"_key":50}]',
            $data
        );
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
            $multi = $loop * 10;
            ++$loop;
            $this->assertStringStartsWith(
                '[{"_key":' . (1 + $multi) . '},{"_key":' . (2 + $multi) . '},{"_key":' . (3 + $multi) . '},{"_key":'
                . (4 + $multi) . '},{"_key":' . (5 + $multi) . '},{"_key":' . (6 + $multi) . '},{"_key":' . (7 + $multi)
                . '},{"_key":' . (8 + $multi) . '},{"_key":' . (9 + $multi) . '},{"_key":' . (10 + $multi) . '}]',
                $data
            );
        }
        $this->assertEquals(5, $loop);
        $this->assertEquals(5, $statement->fetches());
    }
}
