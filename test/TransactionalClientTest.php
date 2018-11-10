<?php
/**
 * Sandro Keil (https://sandro-keil.de)
 *
 * @link      http://github.com/sandrokeil/arangodb-php-client for the canonical source repository
 * @copyright Copyright (c) 2018 Sandro Keil
 * @license   http://github.com/sandrokeil/arangodb-php-client/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ArangoDbTest;

use ArangoDb\TransactionalClient;
use ArangoDb\Type\Collection;
use ArangoDb\Type\Document;
use Fig\Http\Message\StatusCodeInterface;

class TransactionalClientTest extends TestCase
{
    private const COLLECTION_NAME = 'transactionCol';
    private const COLLECTION_NAME_2 = 'transactionCol2';

    /**
     * @var TransactionalClient
     */
    private $transaction;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        TestUtil::getClient()->sendRequest(Collection::create(self::COLLECTION_NAME)->toRequest());
        TestUtil::getClient()->sendRequest(Collection::create(self::COLLECTION_NAME_2)->toRequest());
    }

    protected function setUp()
    {
        parent::setUp();

        $this->transaction = new TransactionalClient($this->client);
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
        $response = $this->transaction->send();

        $content = TestUtil::getResponseContent($response);

        $this->assertEquals(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

        $data = json_decode($content, true);
        $this->arrayHasKey('result', $data);
        $this->arrayHasKey('rId0', $data['result']);
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
        $response = $this->transaction->send();

        $content = TestUtil::getResponseContent($response);

        $this->assertEquals(StatusCodeInterface::STATUS_OK, $response->getStatusCode());

        $data = json_decode($content, true);
        $this->arrayHasKey('result', $data);
        $this->arrayHasKey('rId0', $data['result']);
        $this->arrayHasKey('rId1', $data['result']);
        $this->arrayHasKey('rId2', $data['result']);
    }


}