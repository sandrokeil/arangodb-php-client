<?php
/**
 * Sandro Keil (https://sandro-keil.de)
 *
 * @link      http://github.com/sandrokeil/arangodb-php-client for the canonical source repository
 * @copyright Copyright (c) 2018-2020 Sandro Keil
 * @license   http://github.com/sandrokeil/arangodb-php-client/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ArangoDbTest\Handler;

use ArangoDb\Guard\Guard;
use ArangoDb\Guard\SuccessHttpStatusCode;
use ArangoDb\Handler\Collection;
use ArangoDb\Handler\CollectionHandler;
use ArangoDb\Type\Collection as CollectionType;
use ArangoDb\Type\Document;
use ArangoDbTest\TestCase;
use ArangoDbTest\TestUtil;
use Psr\Http\Message\ResponseInterface;

/**
 * @group handler
 */
final class CollectionTest extends TestCase
{
    private const COLLECTION_NAME = 'col_handler';

    /**
     * @var CollectionHandler
     */
    private $handler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->handler = new Collection($this->client);
    }

    /**
     * @test
     */
    public function it_creates_and_drops_a_collection(): void
    {
        $id = $this->handler->create(self::COLLECTION_NAME);

        $this->assertNotEmpty($id);
        $this->assertTrue($this->handler->has($id));

        $json = $this->handler->get($id)->getBody()->getContents();
        $doc = json_decode($json, true);
        $this->assertSame(self::COLLECTION_NAME, $doc['name']);

        $this->handler->drop($id);
        $this->assertFalse($this->handler->has($id));
    }

    /**
     * @test
     */
    public function it_supports_own_guard(): void
    {
        $guard = new class implements Guard {

            public $invoked = false;

            public function __invoke(ResponseInterface $response): void
            {
                $this->invoked = true;
            }

            public function contentId(): ?string
            {
                return 'test';
            }
        };

        $this->handler = new Collection($this->client, CollectionType::class, $guard);
        $id = $this->handler->create(self::COLLECTION_NAME);

        $this->assertNotEmpty($id);
        $this->assertTrue($guard->invoked);
    }

    /**
     * @test
     */
    public function it_counts_a_collection(): void
    {
        $id = $this->handler->create(self::COLLECTION_NAME);
        $this->assertNotEmpty($id);

        $this->assertTrue($this->handler->has($id));
        $this->assertSame(0, $this->handler->count($id));
    }

    /**
     * @test
     */
    public function it_truncates_a_collection(): void
    {
        $id = $this->handler->create(self::COLLECTION_NAME);
        $this->assertNotEmpty($id);

        $this->assertTrue($this->handler->has($id));
        $this->assertSame(0, $this->handler->count($id));

        $this->client->sendType(
            Document::create(
                self::COLLECTION_NAME,
                ['test' => 'valid']
            )->useGuard(SuccessHttpStatusCode::withoutContentId())
        );

        $this->assertSame(1, $this->handler->count($id));

        $this->handler->truncate(self::COLLECTION_NAME);
        $this->assertSame(0, $this->handler->count($id));
    }


    protected function tearDown(): void
    {
        TestUtil::deleteCollection($this->client, self::COLLECTION_NAME);
    }
}
