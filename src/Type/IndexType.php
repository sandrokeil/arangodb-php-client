<?php
/**
 * Sandro Keil (https://sandro-keil.de)
 *
 * @link      http://github.com/sandrokeil/arangodb-php-client for the canonical source repository
 * @copyright Copyright (c) 2018 Sandro Keil
 * @license   http://github.com/sandrokeil/arangodb-php-client/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ArangoDb\Type;

interface IndexType extends Type
{
    /**
     * @see https://docs.arangodb.com/3.3/HTTP/Indexes/WorkingWith.html#read-all-indexes-of-a-collection
     * @see https://docs.arangodb.com/3.3/Manual/Indexing/WorkingWithIndexes.html#listing-all-indexes-of-a-collection
     *
     * @param string $collectionName
     * @return IndexType
     */
    public static function listAll(string $collectionName): self;

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/Indexes/WorkingWith.html#read-index
     * @see https://docs.arangodb.com/3.3/Manual/Indexing/WorkingWithIndexes.html#fetching-an-index-by-handle
     *
     * @param string $indexName
     * @return IndexType
     */
    public static function info(string $indexName): self;

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/Indexes/WorkingWith.html#create-index
     * @see https://docs.arangodb.com/3.3/Manual/Indexing/WorkingWithIndexes.html#creating-an-index
     *
     * @param string $collectionName
     * @param array $options
     * @return IndexType
     */
    public static function create(string $collectionName, array $options = []): self;

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/Indexes/WorkingWith.html#delete-index
     * @see https://docs.arangodb.com/3.3/Manual/Indexing/WorkingWithIndexes.html#dropping-an-index
     *
     * @param string $indexName
     * @return IndexType
     */
    public static function delete(string $indexName): self;
}
