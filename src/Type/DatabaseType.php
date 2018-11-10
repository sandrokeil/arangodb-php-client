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

interface DatabaseType extends Type
{
    /**
     * @see https://docs.arangodb.com/3.3/HTTP/Database/DatabaseManagement.html#create-database
     *
     * @param string $databaseName
     * @param array $options
     * @return DatabaseType
     */
    public static function create(string $databaseName, array $options = []): self;

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/Database/DatabaseManagement.html#drop-database
     *
     * @param string $databaseName
     * @return DatabaseType
     */
    public static function delete(string $databaseName): self;

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/Database/DatabaseManagement.html#information-of-the-database
     *
     * @return DatabaseType
     */
    public static function info(): self;

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/Database/DatabaseManagement.html#list-of-accessible-databases
     *
     * @return DatabaseType
     */
    public static function listAccessible(): self;

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/Database/DatabaseManagement.html#list-of-databases
     *
     * @return DatabaseType
     */
    public static function listAll(): self;
}
