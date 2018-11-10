<?php
/**
 * Sandro Keil (https://sandro-keil.de)
 *
 * @link      http://github.com/sandrokeil/arangodb-php-client for the canonical source repository
 * @copyright Copyright (c) 2018 Sandro Keil
 * @license   http://github.com/sandrokeil/arangodb-php-client/blob/master/LICENSE.md New BSD License
 */

namespace ArangoDbTest;

use ArangoDb\Client;
use ArangoDb\Statement;
use PHPUnit\Framework\TestCase;

class StatementTest extends TestCase
{
    /**
     * @var Client
     */
    private $client;

    public static function setUpBeforeClass()
    {
        TestUtil::createDatabase();
    }

    public static function tearDownAfterClass()
    {
        TestUtil::dropDatabase();
    }

    protected function setUp()
    {
        $this->client = TestUtil::getClient();
    }

    /**
     * @test
     */
    public function it_returns_data_with_batch_size(): void
    {
        $count = 1000;

        $statement = new Statement(
            $this->client,
            \ArangoDb\Type\CreateCursor::with(
                'FOR i IN 1..' . $count . ' RETURN {"_key": i+1}',
                [],
                $count
            ),
            [
                Statement::ENTRY_TYPE => Statement::ENTRY_TYPE_ARRAY,
            ]
        );
        $this->assertEquals(0, $statement->getFetches());

        $data = [];
        $i = 0;
        foreach ($statement as $key => $item) {
            $this->assertEquals($i, $key);
            $data[] = $item;
            $i++;
        }

        $this->assertNotEmpty($data);
        $this->assertCount($count, $data);
        $this->assertEquals(1, $statement->getFetches());
    }

    /**
     * @test
     */
    public function it_returns_all_data_with_batch_size(): void
    {
        $statement = new Statement(
            $this->client,
            \ArangoDb\Type\CreateCursor::with(
                'FOR i IN 0..99 RETURN {"_key": i+1}',
                [],
                10
            ),
            [
                Statement::ENTRY_TYPE => Statement::ENTRY_TYPE_ARRAY,
            ]
        );
        $this->assertEquals(0, $statement->getFetches());

        $data = [];
        $i = 0;
        foreach ($statement as $key => $item) {
            $this->assertEquals($i, $key);
            $data[] = $item;
            $i++;
        }

        $this->assertNotEmpty($data);
        $this->assertCount(100, $data);
        $this->assertEquals(10, $statement->getFetches());
    }

    /**
     * @test
     */
    public function it_supports_rewind(): void
    {
        $statement = new Statement(
            $this->client,
            \ArangoDb\Type\CreateCursor::with(
                'FOR i IN 0..99 RETURN {"_key": i+1}',
                [],
                10
            ),
            [
                Statement::ENTRY_TYPE => Statement::ENTRY_TYPE_ARRAY,
            ]
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
        $this->assertEquals(10, $statement->getFetches());

        $data = iterator_to_array($statement);
        $this->assertNotEmpty($data);
        $this->assertCount(100, $data);
        $this->assertEquals(10, $statement->getFetches());

        $statement->rewind();
        $this->assertEquals(1, $statement->getFetches());

        $data = $statement->getAll();
        $this->assertNotEmpty($data);
        $this->assertCount(100, $data);
        $this->assertEquals(10, $statement->getFetches());
    }

    /**
     * @test
     */
    public function it_supports_single_object(): void
    {
        $statement = new Statement(
            $this->client,
            \ArangoDb\Type\CreateCursor::with('RETURN {"_key": 1}'),
            [
                Statement::ENTRY_TYPE => Statement::ENTRY_TYPE_ARRAY,
            ]
        );

        $data = $statement->getAll();
        $this->assertNotEmpty($data);
        $this->assertEquals(1, $data[0]['_key']);
        $this->assertEquals(1, $statement->getFetches());
    }

    /**
     * @test
     */
    public function it_supports_arbitrary_data(): void
    {
        $statement = new Statement(
            $this->client,
            \ArangoDb\Type\CreateCursor::with('RETURN "test"'),
            [
                Statement::ENTRY_TYPE => Statement::ENTRY_TYPE_ARRAY,
            ]
        );

        $data = $statement->getAll();
        $this->assertNotEmpty($data);
        $this->assertEquals('test', $data[0]);
        $this->assertEquals(1, $statement->getFetches());
    }

    /**
     * @test
     */
    public function it_supports_json_arbitrary_data(): void
    {
        $statement = new Statement(
            $this->client,
            \ArangoDb\Type\CreateCursor::with('RETURN "test"'),
            [
                Statement::ENTRY_TYPE => Statement::ENTRY_TYPE_JSON,
            ]
        );

        $data = $statement->getAll();
        $this->assertNotEmpty($data);
        $this->assertEquals('["test"]', $data);
        $this->assertEquals(1, $statement->getFetches());
    }

    /**
     * @test
     */
    public function it_returns_all_json_data_with_batch_size(): void
    {
        $statement = new Statement(
            $this->client,
            \ArangoDb\Type\CreateCursor::with(
                'FOR i IN 0..99 RETURN {"_key": i+1}',
                [],
                10
            ),
            [
                Statement::ENTRY_TYPE => Statement::ENTRY_TYPE_JSON,
            ]
        );
        $this->assertEquals(0, $statement->getFetches());

        $data = $statement->getAll();

        $this->assertNotEmpty($data);
        $this->assertStringStartsWith(
            '[{"_key":1},{"_key":2},{"_key":3},{"_key":4},{"_key":5},{"_key":6},{"_key":7},{',
            $data
        );
        $this->assertStringEndsWith(
            '{"_key":93},{"_key":94},{"_key":95},{"_key":96},{"_key":97},{"_key":98},{"_key":99},{"_key":100}]',
            $data
        );
        $this->assertEquals(10, $statement->getFetches());
    }
}