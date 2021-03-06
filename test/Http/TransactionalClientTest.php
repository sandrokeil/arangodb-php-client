<?php
/**
 * Sandro Keil (https://sandro-keil.de)
 *
 * @link      http://github.com/sandrokeil/arangodb-php-client for the canonical source repository
 * @copyright Copyright (c) 2018-2020 Sandro Keil
 * @license   http://github.com/sandrokeil/arangodb-php-client/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ArangoDbTest\Http;

use ArangoDb\Guard\Guard;
use ArangoDb\Http\TransactionalClient;
use ArangoDb\Type\Collection;
use ArangoDb\Type\Document;
use ArangoDb\Type\Index;
use ArangoDbTest\TestCase;
use ArangoDbTest\TestUtil;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;

class TransactionalClientTest extends TestCase
{
    private const COLLECTION_NAME = 'transactionCol';
    private const COLLECTION_NAME_2 = 'transactionCol2';

    /**
     * @var TransactionalClient
     */
    private $transaction;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        TestUtil::getClient()->sendRequest(
            Collection::create(
                self::COLLECTION_NAME,
                [
                    'keyOptions' => [
                        'allowUserKeys' => true,
                        'type' => 'traditional',
                    ],
                ]
            )->toRequest(TestUtil::getRequestFactory(), TestUtil::getStreamFactory())
        );
        TestUtil::getClient()->sendRequest(
            Collection::create(
                self::COLLECTION_NAME_2,
                [
                    'keyOptions' => [
                        'allowUserKeys' => true,
                        'type' => 'traditional',
                    ],
                ]
            )->toRequest(TestUtil::getRequestFactory(), TestUtil::getStreamFactory())
        );
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->transaction = new TransactionalClient(
            $this->client,
            $this->responseFactory
        );
    }

    /**
     * @test
     */
    public function it_handles_empty_types(): void
    {
        $response = $this->transaction->send();

        $this->assertEquals(StatusCodeInterface::STATUS_OK, $response->getStatusCode());
    }

    /**
     * @test
     */
    public function it_can_be_reset(): void
    {

        $this->transaction->add(Document::create(
            self::COLLECTION_NAME,
            [
                ['test3' => 'valid3'],
            ]
        ));
        $this->transaction->add(Index::info('test'));
        $this->assertSame(1, $this->transaction->countTransactionalTypes());
        $this->assertSame(1, $this->transaction->countTypes());

        $this->transaction->reset();

        $this->assertSame(0, $this->transaction->countTransactionalTypes());
        $this->assertSame(0, $this->transaction->countTypes());
    }

    /**
     * @test
     */
    public function it_inserts_documents_at_once(): void
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

        $this->transaction->add($documents);
        $this->assertSame(1, $this->transaction->countTransactionalTypes());
        $this->assertSame(0, $this->transaction->countTypes());
        $response = $this->transaction->send();

        $data = TestUtil::getResponseContent($response);

        $this->assertEquals(
            StatusCodeInterface::STATUS_OK,
            $response->getStatusCode(),
            $response->getBody()->getContents()
        );

        $this->arrayHasKey('result')->evaluate($data);
        $this->arrayHasKey('rId0')->evaluate($data['result']);
    }

    /**
     * @test
     */
    public function it_updates_document(): void
    {
        $documents = Document::create(
            self::COLLECTION_NAME,
            [
                ['_key' => 'test', 'test' => 'valid'],
                ['_key' => 'test2', 'test2' => 'valid2'],
                ['_key' => 'test3', 'test3' => 'valid3'],
            ],
            Document::FLAG_RETURN_NEW
        );

        $update = Document::updateOne(
            self::COLLECTION_NAME . '/' . 'test2',
            [
                'other' => 'value'
            ]
        );

        $this->transaction->addList($documents, $update);
        $response = $this->transaction->send();

        $data = TestUtil::getResponseContent($response);

        $this->assertEquals(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

        $this->assertTrue(isset($data['result']));
        $this->assertTrue(isset($data['result']['rId0']));
        $this->assertTrue(isset($data['result']['rId1']));
    }

    /**
     * @test
     */
    public function it_updates_documents(): void
    {
        $documents = Document::create(
            self::COLLECTION_NAME,
            [
                ['_key' => 'test', 'test' => 'valid'],
                ['_key' => 'test2', 'test2' => 'valid2'],
                ['_key' => 'test3', 'test3' => 'valid3'],
            ],
            Document::FLAG_RETURN_NEW
        );

        $update = Document::update(
            self::COLLECTION_NAME,
            [
                ['_key' => 'test', 'new' => 'value'],
                ['_key' => 'test2', 'other' => 'new'],
                ['_key' => 'test3', 'yeah' => 'works'],
            ]
        );

        $this->transaction->addList($documents, $update);
        $response = $this->transaction->send();

        $data = TestUtil::getResponseContent($response);

        $this->assertEquals(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

        $this->assertTrue(isset($data['result']));
        $this->assertTrue(isset($data['result']['rId0']));
        $this->assertTrue(isset($data['result']['rId1']));
    }

    /**
     * @test
     */
    public function it_replaces_document(): void
    {
        $documents = Document::create(
            self::COLLECTION_NAME,
            [
                ['_key' => 'test', 'test' => 'valid'],
                ['_key' => 'test2', 'test2' => 'valid2'],
                ['_key' => 'test3', 'test3' => 'valid3'],
            ],
            Document::FLAG_RETURN_NEW
        );

        $update = Document::replaceOne(
            self::COLLECTION_NAME . '/' . 'test2',
            [
                'new' => 'value'
            ]
        );

        $this->transaction->addList($documents, $update);
        $response = $this->transaction->send();

        $data = TestUtil::getResponseContent($response);

        $this->assertEquals(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

        $this->assertTrue(isset($data['result']));
        $this->assertTrue(isset($data['result']['rId0']));
        $this->assertTrue(isset($data['result']['rId1']));
    }

    /**
     * @test
     */
    public function it_replaces_documents(): void
    {
        $documents = Document::create(
            self::COLLECTION_NAME,
            [
                ['_key' => 'test', 'test' => 'valid'],
                ['_key' => 'test2', 'test2' => 'valid2'],
                ['_key' => 'test3', 'test3' => 'valid3'],
            ],
            Document::FLAG_RETURN_NEW
        );

        $update = Document::replace(
            self::COLLECTION_NAME,
            [
                ['_key' => 'test', 'new' => 'value'],
                ['_key' => 'test2', 'other' => 'new'],
                ['_key' => 'test3', 'yeah' => 'works'],
            ]
        );

        $this->transaction->addList($documents, $update);
        $response = $this->transaction->send();

        $data = TestUtil::getResponseContent($response);

        $this->assertEquals(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

        $this->assertTrue(isset($data['result']));
        $this->assertTrue(isset($data['result']['rId0']));
        $this->assertTrue(isset($data['result']['rId1']));
    }

    /**
     * @test
     */
    public function it_inserts_documents_in_different_collections(): void
    {
        $this->transaction->add(
            Document::create(
                self::COLLECTION_NAME,
                ['test' => 'valid'],
                Document::FLAG_RETURN_NEW
            )
        );
        $this->transaction->add(
            Document::create(
                self::COLLECTION_NAME_2,
                ['test2' => 'valid2'],
                Document::FLAG_RETURN_NEW
            )
        );
        $this->transaction->add(
            Document::create(
                self::COLLECTION_NAME,
                ['test3' => 'valid3'],
                Document::FLAG_RETURN_NEW
            )
        );
        $index = Index::create(
            self::COLLECTION_NAME_2,
            [
                'type' => 'hash',
                'fields' => [
                    'test2',
                ],
                'selectivityEstimate' => 1,
                'unique' => false,
                'sparse' => false,
            ]
        );

        $this->transaction->add(
            $index
        );
        $this->assertSame(3, $this->transaction->countTransactionalTypes());
        $this->assertSame(1, $this->transaction->countTypes());

        $response = $this->transaction->send();

        $data = TestUtil::getResponseContent($response);

        $this->assertEquals(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

        $this->assertTrue(isset($data['result']));
        $this->assertTrue(isset($data['result']['rId0']));
        $this->assertTrue(isset($data['result']['rId1']));
        $this->arrayHasKey('rId2', $data['result']);

        $response = $this->client->sendRequest(
            Index::listAll(self::COLLECTION_NAME_2)->toRequest($this->requestFactory, $this->streamFactory)
        );
        $this->assertEquals(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

        $data = TestUtil::getResponseContent($response);
        $this->assertArrayHasKey('indexes', $data);
        $this->assertSame('test2', $data['indexes'][1]['fields'][0]);
    }

    /**
     * @test
     */
    public function it_handles_types_with_guards(): void
    {
        $guard = new class () implements Guard {
            public $counter = 0;
            public $name;

            public function __invoke(ResponseInterface $response): void
            {
                $data = TestUtil::getResponseContent($response);
                $this->name = $data['name'];
                $this->counter++;
            }

            public function contentId(): ?string
            {
                return 'test';
            }
        };
        $transactionalGuard = new class () implements Guard {
            public $counter = 0;
            public $validated = false;

            public function __invoke(ResponseInterface $response): void
            {
                $data = TestUtil::getResponseContent($response);
                $this->validated = $data['result']['rIdtransaction']['new']['test2'] === 'valid2';
                $this->counter++;
            }

            public function contentId(): ?string
            {
                return 'transaction';
            }
        };

        $create = Collection::create('xyz');
        $create->useGuard($guard);

        $this->transaction->add($create);

        $this->transaction->add(
            Document::create(
                self::COLLECTION_NAME,
                ['test' => 'valid'],
                Document::FLAG_RETURN_NEW
            )
        );
        $this->transaction->add(
            Document::create(
                self::COLLECTION_NAME_2,
                ['test2' => 'valid2'],
                Document::FLAG_RETURN_NEW
            )->useGuard($transactionalGuard)
        );
        $this->transaction->add(
            Document::create(
                self::COLLECTION_NAME,
                ['test3' => 'valid3'],
                Document::FLAG_RETURN_NEW
            )
        );
        $response = $this->transaction->send();

        $data = TestUtil::getResponseContent($response);

        $this->assertEquals(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

        $this->assertTrue(isset($data['result']));
        $this->assertTrue(isset($data['result']['rId0']));
        $this->assertTrue(isset($data['result']['rId2']));
        $this->assertTrue(isset($data['result']['rIdtransaction']));

        $this->assertSame(1, $guard->counter);
        $this->assertSame('xyz', $guard->name);

        $this->assertSame(1, $transactionalGuard->counter);
        $this->assertTrue($transactionalGuard->validated);
    }
}
