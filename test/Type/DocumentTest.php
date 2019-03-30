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
use ArangoDb\Type\Document;
use ArangoDbTest\TestCase;
use ArangoDbTest\TestUtil;
use Fig\Http\Message\StatusCodeInterface;

class DocumentTest extends TestCase
{
    private const COLLECTION_NAME = 'DocumentTest';

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $createCollection = Collection::create(self::COLLECTION_NAME);
        TestUtil::getClient()->sendRequest(
            $createCollection->toRequest(TestUtil::getRequestFactory(), TestUtil::getStreamFactory())
        );
    }

    /**
     * @test
     */
    public function it_inserts_document(): string
    {
        $response = $this->client->sendRequest(
            Document::create(
                self::COLLECTION_NAME,
                ['test' => 'valid'],
                Document::FLAG_RETURN_NEW
            )->toRequest($this->requestFactory, $this->streamFactory)
        );
        $this->assertEquals(StatusCodeInterface::STATUS_ACCEPTED, $response->getStatusCode());

        $content = TestUtil::getResponseContent($response);
        $data = json_decode($content, true);

        return $data['_id'];
    }

    /**
     * @test
     */
    public function it_inserts_documents(): array
    {
        $documents = Document::create(
            self::COLLECTION_NAME,
            [
                ['test' => 'valid'],
                ['test2' => 'valid2'],
                ['test3' => 'valid3'],
            ],
            Document::FLAG_RETURN_NEW
        );

        $response = $this->client->sendRequest($documents->toRequest($this->requestFactory, $this->streamFactory));

        $content = TestUtil::getResponseContent($response);

        $this->assertEquals(StatusCodeInterface::STATUS_ACCEPTED, $response->getStatusCode());

        $data = json_decode($content, true);

        $this->assertCount(3, $data);

        return array_map(
            function (array $doc) {
                return $doc['_id'];
            },
            $data
        );
    }

    /**
     * @test
     * @depends it_inserts_document
     */
    public function it_reads_document(string $id): string
    {
        $response = $this->client->sendRequest(Document::read($id)->toRequest($this->requestFactory, $this->streamFactory));

        $content = TestUtil::getResponseContent($response);

        $this->assertEquals(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

        $data = json_decode($content, true);
        $this->assertSame('valid', $data['test'] ?? '');

        return $data['_id'];
    }

    /**
     * @test
     * @depends it_reads_document
     */
    public function it_deletes_document(string $id): void
    {
        $response = $this->client->sendRequest(
            Document::deleteOne($id)->toRequest($this->requestFactory, $this->streamFactory)
        );

        $this->assertEquals(StatusCodeInterface::STATUS_ACCEPTED, $response->getStatusCode());
    }

    /**
     * @test
     * @depends it_inserts_documents
     */
    public function it_deletes_documents(array $keys): void
    {
        $response = $this->client->sendRequest(
            Document::delete(self::COLLECTION_NAME, $keys)->toRequest($this->requestFactory, $this->streamFactory)
        );

        $this->assertEquals(StatusCodeInterface::STATUS_ACCEPTED, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function it_updates_document(): void
    {
        $response = $this->client->sendRequest(
            Document::create(
                self::COLLECTION_NAME,
                ['test' => 'valid', 'foo' => 'bar'],
                Document::FLAG_RETURN_NEW
            )->toRequest($this->requestFactory, $this->streamFactory)
        );

        $content = TestUtil::getResponseContent($response);
        $this->assertEquals(StatusCodeInterface::STATUS_ACCEPTED, $response->getStatusCode());

        $data = json_decode($content, true);

        $response = $this->client->sendRequest(
            Document::updateOne($data['_id'], ['test' => 'more valid'], Document::FLAG_RETURN_NEW)
                ->toRequest($this->requestFactory, $this->streamFactory)
        );

        $content = TestUtil::getResponseContent($response);
        $this->assertEquals(StatusCodeInterface::STATUS_ACCEPTED, $response->getStatusCode());

        $data = json_decode($content, true);
        $this->assertSame('more valid', $data['new']['test'] ?? '');
        $this->assertSame('bar', $data['new']['foo'] ?? '');
    }

    /**
     * @test
     */
    public function it_updates_documents(): void
    {
        $documents = Document::create(
            self::COLLECTION_NAME,
            [
                ['test' => 'valid'],
                ['test2' => 'valid2'],
                ['test3' => 'valid3'],
            ],
            Document::FLAG_RETURN_NEW
        );

        $response = $this->client->sendRequest($documents->toRequest($this->requestFactory, $this->streamFactory));

        $content = TestUtil::getResponseContent($response);

        $this->assertEquals(StatusCodeInterface::STATUS_ACCEPTED, $response->getStatusCode());

        $data = json_decode($content, true);

        $data = array_map(
            function (array $doc) {
                $newDoc = $doc['new'];
                $newDoc['test'] = 'more valid';
                return $newDoc;
            },
            $data
        );

        $response = $this->client->sendRequest(
            Document::update(self::COLLECTION_NAME, $data, Document::FLAG_RETURN_NEW)->toRequest($this->requestFactory, $this->streamFactory)
        );

        $content = TestUtil::getResponseContent($response);
        $this->assertEquals(StatusCodeInterface::STATUS_ACCEPTED, $response->getStatusCode());

        $data = json_decode($content, true);
        $this->assertCount(3, $data);
        $this->assertSame('more valid', $data[0]['new']['test'] ?? '');
        $this->assertSame('more valid', $data[1]['new']['test'] ?? '');
        $this->assertSame('more valid', $data[2]['new']['test'] ?? '');
    }

    /**
     * @test
     */
    public function it_replaces_document(): void
    {
        $response = $this->client->sendRequest(
            Document::create(
                self::COLLECTION_NAME,
                ['test' => 'valid', 'foo' => 'bar'],
                Document::FLAG_RETURN_NEW
            )->toRequest($this->requestFactory, $this->streamFactory)
        );

        $content = TestUtil::getResponseContent($response);
        $this->assertEquals(StatusCodeInterface::STATUS_ACCEPTED, $response->getStatusCode());

        $data = json_decode($content, true);

        $response = $this->client->sendRequest(
            Document::replaceOne($data['_id'], ['other' => 'more valid'], Document::FLAG_RETURN_NEW)
                ->toRequest($this->requestFactory, $this->streamFactory)
        );

        $content = TestUtil::getResponseContent($response);
        $this->assertEquals(StatusCodeInterface::STATUS_ACCEPTED, $response->getStatusCode());

        $data = json_decode($content, true);
        $this->assertSame('more valid', $data['new']['other'] ?? '');
        $this->assertArrayNotHasKey('test', $data['new']);
    }


    /**
     * @test
     */
    public function it_replaces_documents(): void
    {
        $documents = Document::create(
            self::COLLECTION_NAME,
            [
                ['test' => 'valid'],
                ['test' => 'valid2'],
                ['test' => 'valid3'],
            ],
            Document::FLAG_RETURN_NEW
        );

        $response = $this->client->sendRequest($documents->toRequest($this->requestFactory, $this->streamFactory));

        $content = TestUtil::getResponseContent($response);

        $this->assertEquals(StatusCodeInterface::STATUS_ACCEPTED, $response->getStatusCode());

        $data = json_decode($content, true);

        $data = array_map(
            function (array $doc) {
                $newDoc = $doc['new'];
                unset($newDoc['test']);
                $newDoc['other'] = 'more valid';
                return $newDoc;
            },
            $data
        );

        $response = $this->client->sendRequest(
            Document::replace(self::COLLECTION_NAME, $data, Document::FLAG_RETURN_NEW)->toRequest($this->requestFactory, $this->streamFactory)
        );

        $content = TestUtil::getResponseContent($response);
        $this->assertEquals(StatusCodeInterface::STATUS_ACCEPTED, $response->getStatusCode());

        $data = json_decode($content, true);
        $this->assertCount(3, $data);
        $this->assertSame('more valid', $data[0]['new']['other'] ?? '');
        $this->assertArrayNotHasKey('test', $data[0]['new']);
        $this->assertSame('more valid', $data[1]['new']['other'] ?? '');
        $this->assertArrayNotHasKey('test', $data[2]['new']);
        $this->assertSame('more valid', $data[2]['new']['other'] ?? '');
        $this->assertArrayNotHasKey('test', $data[2]['new']);
    }
}
