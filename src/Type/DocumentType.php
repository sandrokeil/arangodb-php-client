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

interface DocumentType extends GuardSupport
{
    public const FLAG_RETURN_OLD = 1;
    public const FLAG_WAIT_FOR_SYNC = 2;
    public const FLAG_SILENT = 4;
    public const FLAG_RETURN_NEW = 8;
    public const FLAG_CHECK_REVS = 16;
    public const FLAG_KEEP_NULL = 32;
    public const FLAG_REPLACE_OBJECTS = 64;
    public const FLAG_REMOVE_NULL = 128;

    public const TYPE_ID = 'id';
    public const TYPE_KEY = 'key';
    public const TYPE_PATH = 'path';

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/Document/WorkingWithDocuments.html#read-document
     * @see https://docs.arangodb.com/3.3/Manual/DataModeling/Documents/DocumentMethods.html#document
     *
     * @param string $id
     * @return DocumentType
     */
    public static function read(string $id): self;

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/Document/WorkingWithDocuments.html#read-document-header
     *
     * @param string $id
     * @return DocumentType
     */
    public static function readHeader(string $id): self;

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/Document/WorkingWithDocuments.html#read-all-documents
     *
     * @param string $collectionName
     * @param string $type
     * @return DocumentType
     */
    public static function readAll(string $collectionName, string $type): self;

    /**
     * @see https://docs.arangodb.com/3.3/Manual/DataModeling/Documents/DocumentMethods.html#insert
     * @see https://docs.arangodb.com/3.3/HTTP/Document/WorkingWithDocuments.html#create-document
     *
     * @param string $collectionName
     * @param array $docs
     * @param int $flags FLAG_RETURN_NEW | FLAG_WAIT_FOR_SYNC | FLAG_SILENT
     * @return DocumentType
     */
    public static function create(
        string $collectionName,
        array $docs,
        int $flags = 0
    ): self;

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/Document/WorkingWithDocuments.html#removes-multiple-documents
     * @see https://docs.arangodb.com/3.3/Manual/DataModeling/Documents/DocumentMethods.html#remove-by-keys
     *
     * @param string $collectionName
     * @param array $keys
     * @param int $flags
     * @return DocumentType
     */
    public static function delete(
        string $collectionName,
        array $keys,
        int $flags = 0
    ): self;

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/Document/WorkingWithDocuments.html#removes-a-document
     * @see https://docs.arangodb.com/3.3/Manual/DataModeling/Documents/DatabaseMethods.html#remove
     *
     * @param string $id
     * @param int $flags
     * @return DocumentType
     */
    public static function deleteOne(
        string $id,
        int $flags = 0
    ): DocumentType;

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/SimpleQuery/#remove-documents-by-example
     * @see https://docs.arangodb.com/3.3/Manual/DataModeling/Documents/DocumentMethods.html#remove-by-example
     *
     * @param string $collectionName
     * @param array $example
     * @return DocumentType
     */
    public static function deleteBy(
        string $collectionName,
        array $example
    ): DocumentType;

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/Document/WorkingWithDocuments.html#update-documents
     * @see https://docs.arangodb.com/3.3/Manual/DataModeling/Documents/DocumentMethods.html#update
     *
     * @param string $collectionName
     * @param array $data
     * @param int $flags
     * @return DocumentType
     */
    public static function update(
        string $collectionName,
        array $data,
        int $flags = 0
    ): DocumentType;

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/Document/WorkingWithDocuments.html#update-document
     * @see https://docs.arangodb.com/3.3/Manual/DataModeling/Documents/DocumentMethods.html#update
     *
     * @param string $id
     * @param array $data
     * @param int $flags
     * @return DocumentType
     */
    public static function updateOne(
        string $id,
        array $data,
        int $flags = 0
    ): DocumentType;

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/Document/WorkingWithDocuments.html#replace-documents
     * @see https://docs.arangodb.com/3.3/Manual/DataModeling/Documents/DocumentMethods.html#replace
     *
     * @param string $collectionName
     * @param array $data
     * @param int $flags
     * @return DocumentType
     */
    public static function replace(
        string $collectionName,
        array $data,
        int $flags = 0
    ): DocumentType;

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/Document/WorkingWithDocuments.html#replace-document
     * @see https://docs.arangodb.com/3.3/Manual/DataModeling/Documents/DocumentMethods.html#replace
     *
     * @param string $id
     * @param array $data
     * @param int $flags
     * @return DocumentType
     */
    public static function replaceOne(
        string $id,
        array $data,
        int $flags = 0
    ): DocumentType;
}
