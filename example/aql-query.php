<?php
/**
 * Sandro Keil (https://sandro-keil.de)
 *
 * @link      http://github.com/sandrokeil/arangodb-php-client for the canonical source repository
 * @copyright Copyright (c) 2018-2020 Sandro Keil
 * @license   http://github.com/sandrokeil/arangodb-php-client/blob/master/LICENSE.md New BSD License
 */

require __DIR__ . '/init.php';

use ArangoDb\Statement\Statement;
use ArangoDb\Type\Batch;
use ArangoDb\Type\Collection;
use ArangoDb\Type\Cursor;
use ArangoDb\Type\Document;
use ArangoDbTest\TestUtil;

$client = TestUtil::getClient();
$requestFactory = TestUtil::getRequestFactory();
$streamFactory = TestUtil::getStreamFactory();
$streamHandlerFactory = TestUtil::getStreamHandlerFactory();

$collectionName = 'users';

/* set up some example statements */
$statements = [
    'FOR u IN users RETURN u'                                       => [],
    'FOR u IN users FILTER u.id == @id RETURN u'                    => ['id' => 6],
    'FOR u IN users FILTER u.id == @id && u.name != @name RETURN u' => ['id' => 1, 'name' => 'fox'],
];

try {
    $t1 = microtime(true);

    // create collection users
    $collectionRequest = Collection::create($collectionName)->toRequest($requestFactory, $streamFactory);
    $collectionResponse = $client->sendRequest($collectionRequest);

    // create documents for batch request
    $docs = [
        Document::create($collectionName, ['name' => 'foo', 'id' => 1]),
        Document::create($collectionName, ['name' => 'bar', 'id' => 2]),
        Document::create($collectionName, ['name' => 'baz', 'id' => 3]),
        Document::create($collectionName, ['name' => 'fox', 'id' => 4]),
        Document::create($collectionName, ['name' => 'qaa', 'id' => 5]),
        Document::create($collectionName, ['name' => 'qux', 'id' => 6]),
        Document::create($collectionName, ['name' => 'quu', 'id' => 7]),
    ];

    $batchRequest = Batch::fromTypes(...$docs)->toRequest($requestFactory, $streamFactory);

    $batchResponse = $client->sendRequest($batchRequest);

    foreach ($statements as $query => $bindVars) {
        $statement = new Statement(
            $client,
            Cursor::create($query, $bindVars, 1000, true)->toRequest($requestFactory, $streamFactory),
            $requestFactory,
            $streamHandlerFactory
        );

        echo 'RUNNING STATEMENT ' . $query . PHP_EOL;

        foreach ($statement->fetchAll() as $doc) {
            echo '- RETURN VALUE: ' . json_encode($doc) . PHP_EOL;
        }

        echo PHP_EOL;
    }

    $t2 = microtime(true);
    $totalTime = ($t2 - $t1);

    echo sprintf('Execution time %s s', $totalTime) . PHP_EOL;
} catch (\Throwable $e) {
    print_r($e);
}
