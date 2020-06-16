<?php
/**
 * Sandro Keil (https://sandro-keil.de)
 *
 * @link      http://github.com/sandrokeil/arangodb-php-client for the canonical source repository
 * @copyright Copyright (c) 2018-2020 Sandro Keil
 * @license   http://github.com/sandrokeil/arangodb-php-client/blob/master/LICENSE.md New BSD License
 */

require __DIR__ . '/init.php';

use ArangoDb\Handler\Collection;
use ArangoDb\Handler\Document;
use ArangoDbTest\TestUtil;

$client = TestUtil::getClient();

$collectionName = 'document_handler';

$collectionHandler = new Collection($client);
$documentHandler = new Document($client);

try {
    $t1 = microtime(true);

    if (false === $collectionHandler->has($collectionName)) {
        echo 'SENDING CREATING COLLECTION ' . $collectionName . ' ...' . PHP_EOL;
        $documentId = $collectionHandler->create($collectionName);
        echo 'COLLECTION WITH ID ' . $documentId . ' CREATED' . PHP_EOL;
    } else {
        echo 'COLLECTION ' . $collectionName . ' ALREADY EXISTS.' . PHP_EOL;
    }

    echo 'SAVE DOCUMENT IN COLLECTION ' . $collectionName . ' ...' . PHP_EOL;
    $documentId = $documentHandler->save($collectionName, ['test' => 'success']);
    echo 'DOCUMENT WITH ID ' . $documentId . ' CREATED' . PHP_EOL;

    echo 'SENDING GET DOCUMENT ' . $documentId . ' ' . PHP_EOL;
    print_r($documentHandler->get($documentId)->getBody()->getContents() . PHP_EOL);

    echo 'SENDING HAS DOCUMENT ' . $documentId . ' ';
    echo ($documentHandler->has($documentId) ? 'true' : 'false') . PHP_EOL;

    echo 'SENDING DELETE DOCUMENT ' . $documentId . ' ' . PHP_EOL;
    $documentHandler->remove($documentId);

    echo 'SENDING HAS DOCUMENT ' . $documentId . ' ';
    echo ($documentHandler->has($documentId) ? 'true' : 'false') . PHP_EOL;

    $t2 = microtime(true);
    $totalTime = ($t2 - $t1);

    echo PHP_EOL . sprintf('Execution time %s s', $totalTime) . PHP_EOL;
} catch (\Throwable $e) {
    print_r($e);
}
