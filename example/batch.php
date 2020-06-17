<?php
/**
 * Sandro Keil (https://sandro-keil.de)
 *
 * @link      http://github.com/sandrokeil/arangodb-php-client for the canonical source repository
 * @copyright Copyright (c) 2018-2020 Sandro Keil
 * @license   http://github.com/sandrokeil/arangodb-php-client/blob/master/LICENSE.md New BSD License
 */

require __DIR__ . '/init.php';

use ArangoDb\Guard\SuccessHttpStatusCode;
use ArangoDb\Http\BatchResult;
use ArangoDb\Type\Batch;
use ArangoDb\Type\Collection;
use ArangoDb\Type\Document;
use ArangoDbTest\TestUtil;

$client = TestUtil::getClient();
$requestFactory = TestUtil::getRequestFactory();
$responseFactory = TestUtil::getResponseFactory();
$streamFactory = TestUtil::getStreamFactory();
$streamHandlerFactory = TestUtil::getStreamHandlerFactory();

$collectionName = 'users';

$guard = SuccessHttpStatusCode::withoutContentId();

try {
    $t1 = microtime(true);

    // creating types for batch request
    $types = [
        Collection::create($collectionName)->useGuard($guard),
        Document::create($collectionName, ['name' => 'foo', 'id' => 1])->useGuard($guard),
        Document::create($collectionName, ['name' => 'qux', 'id' => 6])->useGuard($guard),
        Document::create($collectionName, ['name' => 'quu', 'id' => 7])->useGuard($guard),
    ];

    $batch = Batch::fromTypes(...$types);
    $batchRequest = Batch::fromTypes(...$types)->toRequest($requestFactory, $streamFactory);

    echo 'SENDING BATCH WITH CREATING COLLECTION ' . $collectionName . ' and 3 docs ...';
    $batchResponse = $client->sendRequest($batchRequest);
    echo ' Status Code: ' . $batchResponse->getStatusCode() . PHP_EOL;

    $batchResult = BatchResult::fromResponse($batchResponse, $responseFactory, $streamFactory);

    echo 'VALIDATE BATCH ' . PHP_EOL;
    $batchResult->validateBatch($batch);

    echo 'DONE' . PHP_EOL;

    $t2 = microtime(true);
    $totalTime = ($t2 - $t1);

    echo sprintf('Execution time %s s', $totalTime) . PHP_EOL;
} catch (\Throwable $e) {
    print_r($e);
}
