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

use ArangoDb\Exception\LogicException;
use ArangoDb\VpackStream;
use ArangoDBClient\Urls;
use Fig\Http\Message\RequestMethodInterface;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;

final class DeleteDatabase implements Type
{
    /**
     * @var string
     */
    private $databaseName;

    /**
     * @var array
     */
    private $options;

    private function __construct(string $databaseName, array $options = [])
    {
        $this->databaseName = $databaseName;
        $this->options = $options;
    }

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/Database/DatabaseManagement.html#drop-database
     *
     * @param string $databaseName
     * @param array $options
     * @return DeleteDatabase
     */
    public static function with(string $databaseName, array $options = []): DeleteDatabase
    {
        return new self($databaseName, $options);
    }

    public function toRequest(): RequestInterface
    {
        return new Request(
            RequestMethodInterface::METHOD_DELETE,
            Urls::URL_DATABASE . '/' . $this->databaseName,
            [],
            new VpackStream($this->options)
        );
    }

    public function toJs(): string
    {
        throw new LogicException('Not possible at the moment, see ArangoDB docs');
    }
}
