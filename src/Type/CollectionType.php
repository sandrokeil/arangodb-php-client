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

/**
 * @see https://docs.arangodb.com/3.3/HTTP/Collection/
 * @see https://docs.arangodb.com/3.3/Manual/Appendix/References/CollectionObject.html
 */
interface CollectionType extends Type
{
    /**
     * Create new collection
     *
     * @see https://docs.arangodb.com/3.3/HTTP/Collection/Creating.html#create-collection
     * @see https://docs.arangodb.com/3.3/Manual/DataModeling/Collections/DatabaseMethods.html#create
     *
     * @param string $collectionName
     * @param array $options
     * @return CollectionType
     */
    public static function create(string $collectionName, array $options = []): self;

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/Collection/Getting.html#reads-all-collections
     * @see https://docs.arangodb.com/3.3/Manual/DataModeling/Collections/DatabaseMethods.html#all-collections
     *
     * @param bool $excludeSystem
     * @return CollectionType
     */
    public static function listAll(bool $excludeSystem = true): self;

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/Collection/Getting.html#return-information-about-a-collection
     *
     * @param string $collectionName
     * @return CollectionType
     */
    public static function info(string $collectionName): self;

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/Collection/Getting.html#return-checksum-for-the-collection
     * @see https://docs.arangodb.com/3.3/Manual/DataModeling/Collections/CollectionMethods.html#checksum
     *
     * @param string $collectionName
     * @return CollectionType
     */
    public static function checksum(string $collectionName): self;

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/Collection/Getting.html#return-number-of-documents-in-a-collection
     * @see https://docs.arangodb.com/3.3/Manual/DataModeling/Documents/DocumentMethods.html#count
     *
     * @param string $collectionName
     * @return CollectionType
     */
    public static function count(string $collectionName): self;

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/Collection/Getting.html#return-statistics-for-a-collection
     * @see https://docs.arangodb.com/3.3/Manual/DataModeling/Collections/CollectionMethods.html#figures
     *
     * @param string $collectionName
     * @return CollectionType
     */
    public static function figures(string $collectionName): self;

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/Collection/Getting.html#read-properties-of-a-collection
     * @see https://docs.arangodb.com/3.3/Manual/DataModeling/Collections/CollectionMethods.html#properties
     *
     * @param string $collectionName
     * @return CollectionType
     */
    public static function properties(string $collectionName): self;

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/Collection/Getting.html#return-collection-revision-id
     * @see https://docs.arangodb.com/3.3/Manual/DataModeling/Collections/CollectionMethods.html#revision
     *
     * @param string $collectionName
     * @return CollectionType
     */
    public static function revision(string $collectionName): self;

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/Collection/Creating.html#drops-a-collection
     * @see https://docs.arangodb.com/3.3/Manual/DataModeling/Collections/CollectionMethods.html#drop
     *
     * @param string $collectionName
     * @return CollectionType
     */
    public static function delete(string $collectionName): self;

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/Collection/Modifying.html#load-collection
     * @see https://docs.arangodb.com/3.3/Manual/DataModeling/Collections/CollectionMethods.html#load
     *
     * @param string $collectionName
     * @return CollectionType
     */
    public static function load(string $collectionName): self;

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/Collection/Modifying.html#load-indexes-into-memory
     *
     * @param string $collectionName
     * @return CollectionType
     */
    public static function loadIndexes(string $collectionName): self;

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/Collection/Modifying.html#change-properties-of-a-collection
     * @see https://docs.arangodb.com/3.3/Manual/DataModeling/Collections/CollectionMethods.html#properties
     *
     * @param string $collectionName
     * @param bool|null $waitForSync
     * @param int|null $journalSize
     * @return CollectionType
     */
    public static function updateProperties(
        string $collectionName,
        bool $waitForSync = null,
        int $journalSize = null
    ): self;

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/Collection/Modifying.html#rename-collection
     * @see https://docs.arangodb.com/3.3/Manual/DataModeling/Collections/CollectionMethods.html#rename
     *
     * @param string $collectionName
     * @param string $newCollectionName
     * @return CollectionType
     */
    public static function rename(string $collectionName, string $newCollectionName): self;

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/Collection/Modifying.html#rotate-journal-of-a-collection
     * @see https://docs.arangodb.com/3.3/Manual/DataModeling/Collections/CollectionMethods.html#rotate
     *
     * @param string $collectionName
     * @return CollectionType
     */
    public static function rotate(string $collectionName): self;

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/Collection/Creating.html#truncate-collection
     * @see https://docs.arangodb.com/3.3/Manual/DataModeling/Collections/CollectionMethods.html#truncate
     *
     * @param string $collectionName
     * @return CollectionType
     */
    public static function truncate(string $collectionName): self;

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/Collection/Modifying.html#unload-collection
     * @see https://docs.arangodb.com/3.3/Manual/DataModeling/Collections/CollectionMethods.html#unload
     *
     * @param string $collectionName
     * @return CollectionType
     */
    public static function unload(string $collectionName): self;
}
