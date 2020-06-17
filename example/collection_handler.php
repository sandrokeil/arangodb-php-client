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
use ArangoDbTest\TestUtil;

$client = TestUtil::getClient();

$collectionName = 'collection_handler';

$collectionHandler = new Collection($client);

try {
    $t1 = microtime(true);

    if (false === $collectionHandler->has($collectionName)) {
        echo 'SENDING CREATING COLLECTION ' . $collectionName . ' ...' . PHP_EOL;
        $id = $collectionHandler->create($collectionName);
        echo 'COLLECTION WITH ID ' . $id . ' CREATED' . PHP_EOL;
    } else {
        echo 'COLLECTION ' . $collectionName . ' ALREADY EXISTS.' . PHP_EOL;
    }
    echo 'GET COLLECTION INFO FOR ' . $collectionName . ' ...' . PHP_EOL;
    print_r($collectionHandler->get($collectionName)->getBody()->getContents());
    echo PHP_EOL;

    echo 'NUMBER OF DOCS IN COLLECTION ' . $collectionName . ' ' . $collectionHandler->count($collectionName) . PHP_EOL;

    echo 'SENDING TRUNCATE COLLECTION ' . $collectionName . ' ' . PHP_EOL;
    $collectionHandler->truncate($collectionName);

    echo 'SENDING DROP COLLECTION ' . $collectionName . ' ' . PHP_EOL;
    $collectionHandler->drop($collectionName);

    $t2 = microtime(true);
    $totalTime = ($t2 - $t1);

    echo sprintf('Execution time %s s', $totalTime) . PHP_EOL;
} catch (\Throwable $e) {
    print_r($e);
}
