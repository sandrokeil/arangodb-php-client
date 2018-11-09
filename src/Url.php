<?php
/**
 * Sandro Keil (https://sandro-keil.de)
 *
 * @link      http://github.com/sandrokeil/arangodb-php-client for the canonical source repository
 * @copyright Copyright (c) 2018 Sandro Keil
 * @license   http://github.com/sandrokeil/arangodb-php-client/blob/master/LICENSE.md New BSD License
 */

namespace ArangoDb;

class Url
{
    /**
     * @see https://docs.arangodb.com/3.3/HTTP/Transaction/
     */
    public const TRANSACTION = '/_api/transaction';

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/Database/DatabaseManagement.html
     */
    public const DATABASE = '/_api/database';

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/Collection/
     */
    public const COLLECTION = '/_api/collection';

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/Indexes/WorkingWith.html
     */
    public const INDEX = '/_api/index';

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/AqlQueryCursor/QueryResults.html
     */
    public const CURSOR = '/_api/cursor';

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/Document/WorkingWithDocuments.html
     */
    public const DOCUMENT = '/_api/document';

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/Document/WorkingWithDocuments.html
     */
    public const EDGE = '/_api/document';

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/Edge/WorkingWithEdges.html
     */
    public const EDGES = '/_api/edges';

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/Gharial/Management.html
     */
    public const GRAPH = '/_api/gharial';

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/Gharial/Vertices.html
     */
    public const PART_VERTEX = 'vertex';

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/Gharial/Edges.html
     */
    public const PART_EDGE = 'edge';

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/Export/
     */
    public const EXPORT = '/_api/export';

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/AqlQuery/
     */
    public const EXPLAIN = '/_api/explain';

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/AqlQuery/#parse-an-aql-query
     */
    public const QUERY = '/_api/query';

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/SimpleQuery/
     */
    public const EXAMPLE = '/_api/simple/by-example';

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/SimpleQuery/
     */
    public const FIRST_EXAMPLE = '/_api/simple/first-example';

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/SimpleQuery/
     */
    public const ANY = '/_api/simple/any';

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/SimpleQuery/
     */
    public const FULLTEXT = '/_api/simple/fulltext';

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/SimpleQuery/
     */
    public const REMOVE_BY_EXAMPLE = '/_api/simple/remove-by-example';

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/SimpleQuery/
     */
    public const REMOVE_BY_KEYS = '/_api/simple/remove-by-keys';

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/SimpleQuery/
     */
    public const UPDATE_BY_EXAMPLE = '/_api/simple/update-by-example';

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/SimpleQuery/
     */
    public const REPLACE_BY_EXAMPLE = '/_api/simple/replace-by-example';

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/SimpleQuery/
     */
    public const LOOKUP_BY_KEYS = '/_api/simple/lookup-by-keys';

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/SimpleQuery/
     */
    public const RANGE = '/_api/simple/range';

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/SimpleQuery/
     */
    public const ALL = '/_api/simple/all';

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/SimpleQuery/
     */
    public const ALL_KEYS = '/_api/simple/all-keys';

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/SimpleQuery/
     */
    public const NEAR = '/_api/simple/near';

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/SimpleQuery/
     */
    public const WITHIN = '/_api/simple/within';

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/BulkImports/
     */
    public const IMPORT = '/_api/import';

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/BatchRequest/
     */
    public const BATCH = '/_api/batch';

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/MiscellaneousFunctions/#return-server-database-engine-type
     */
    public const ENGINE = '/_api/engine';

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/MiscellaneousFunctions/#return-server-version
     */
    public const ADMIN_VERSION = '/_api/version';

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/AdministrationAndMonitoring/#return-role-of-a-server-in-a-cluster
     */
    public const ADMIN_SERVER_ROLE = '/_admin/server/role';

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/MiscellaneousFunctions/#return-system-time
     */
    public const ADMIN_TIME = '/_admin/time';

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/AdministrationAndMonitoring/
     */
    public const ADMIN_LOG = '/_admin/log';

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/AdministrationAndMonitoring/#reloads-the-routing-information
     */
    public const ADMIN_ROUTING_RELOAD = '/_admin/routing/reload';

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/AdministrationAndMonitoring/#read-the-statistics
     */
    public const ADMIN_STATISTICS = '/_admin/statistics';

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/AdministrationAndMonitoring/#statistics-description
     */
    public const ADMIN_STATISTICS_DESCRIPTION = '/_admin/statistics-description';

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/AqlUserFunctions/
     */
    public const AQL_USER_FUNCTION = '/_api/aqlfunction';

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/UserManagement/
     */
    public const USER = '/_api/user';

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/Traversal/#traversals
     */
    public const TRAVERSAL = '/_api/traversal';

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/Endpoints/#get-information-about-all-coordinator-endpoints
     */
    public const ENDPOINT = '/_api/cluster/endpoint';

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/AqlQueryCache/
     */
    public const QUERY_CACHE = '/_api/query-cache';
}
