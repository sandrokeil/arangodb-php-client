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
use ArangoDb\Handler\Document;
use ArangoDb\Handler\DocumentHandler;
use ArangoDb\Type\Collection as CollectionType;
use ArangoDb\Type\Document as DocumentType;
use ArangoDbTest\TestCase;
use ArangoDbTest\TestUtil;
use Psr\Http\Message\ResponseInterface;

/**
 * @group handler
 */
final class DocumentTest extends TestCase
{
    private const COLLECTION_NAME = 'doc_handler';

    /**
     * @var DocumentHandler
     */
    private $handler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->handler = new Document($this->client);
        $this->client->sendType(
            CollectionType::create(self::COLLECTION_NAME)->useGuard(SuccessHttpStatusCode::withoutContentId())
        );
    }

    /**
     * @test
     */
    public function it_creates_and_retrieves_a_document(): void
    {
        $documentId = $this->handler->save(self::COLLECTION_NAME, ['test' => 'works']);

        $this->assertNotEmpty($documentId);
        $this->assertTrue($this->handler->has($documentId));

        $json = $this->handler->get($documentId)->getBody()->getContents();
        $doc = json_decode($json, true);
        $this->assertSame('works', $doc['test']);
    }

    /**
     * @test
     */
    public function it_creates_and_retrieves_a_document_by_id(): void
    {
        $documentId = $this->handler->save(self::COLLECTION_NAME, ['test' => 'works']);
        $id = str_replace(self::COLLECTION_NAME . '/', '', $documentId);

        $this->assertNotEmpty($documentId);
        $this->assertTrue($this->handler->hasById(self::COLLECTION_NAME, $id));

        $json = $this->handler->getById(self::COLLECTION_NAME, $id)->getBody()->getContents();
        $doc = json_decode($json, true);
        $this->assertSame('works', $doc['test']);
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

        $this->handler = new Document($this->client, DocumentType::class, $guard);
        $id = $this->handler->save(self::COLLECTION_NAME, ['test' => 'works']);

        $this->assertNotEmpty($id);
        $this->assertTrue($guard->invoked);
    }

    /**
     * @test
     */
    public function it_removes_a_document(): void
    {
        $documentId = $this->handler->save(self::COLLECTION_NAME, ['test' => 'works']);

        $this->assertNotEmpty($documentId);
        $this->assertTrue($this->handler->has($documentId));

        $this->handler->remove($documentId);
        $this->assertFalse($this->handler->has($documentId));
    }

    /**
     * @test
     */
    public function it_removes_a_document_by_id(): void
    {
        $documentId = $this->handler->save(self::COLLECTION_NAME, ['test' => 'works']);
        $id = str_replace(self::COLLECTION_NAME . '/', '', $documentId);

        $this->assertNotEmpty($documentId);
        $this->assertTrue($this->handler->hasById(self::COLLECTION_NAME, $id));

        $this->handler->removeById(self::COLLECTION_NAME, $id);
        $this->assertFalse($this->handler->hasById(self::COLLECTION_NAME, $id));
    }

    protected function tearDown(): void
    {
        TestUtil::deleteCollection($this->client, self::COLLECTION_NAME);
    }
}
