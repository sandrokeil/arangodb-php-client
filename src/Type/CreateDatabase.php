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

final class CreateDatabase implements Type
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $options;

    private function __construct(string $name, array $options = [])
    {
        $this->name = $name;
        $this->options = $options;
    }

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/Database/DatabaseManagement.html#create-database
     *
     * @param string $databaseName
     * @param array $options
     * @return CreateDatabase
     */
    public static function with(string $databaseName, array $options = []): CreateDatabase
    {
        return new self($databaseName, $options);
    }

    public function toRequest(): RequestInterface
    {
        $options = $this->options;
        $options['name'] = $this->name;

        return new Request(
            RequestMethodInterface::METHOD_POST,
            Urls::URL_DATABASE,
            [],
            new VpackStream($options)
        );
    }

    public function toJs(): string
    {
        throw new LogicException('Not possible at the moment, see ArangoDB docs');
    }
}
