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
use Psr\Http\Message\ResponseInterface;

class CreateDatabase implements Type
{
    use ToHttpTrait;

    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $options;

    /**
     * Inspects response
     *
     * @var callable
     */
    private $inspector;

    private function __construct(
        string $name,
        array $options = [],
        callable $inspector = null
    ) {
        $this->name = $name;
        $this->options = $options;
        $this->inspector = $inspector ?: function (ResponseInterface $response, string $rId = null) {
            return null;
        };
    }

    /**
     * @see https://docs.arangodb.com/3.2/HTTP/Database/DatabaseManagement.html#create-database
     *
     * @param string $databaseName
     * @param array $options
     * @return CreateDatabase
     */
    public static function with(string $databaseName, array $options = []): CreateDatabase
    {
        return new self($databaseName, $options);
    }

    /**
     * @see https://docs.arangodb.com/3.2/HTTP/Database/DatabaseManagement.html#create-database
     *
     * @param string $databaseName
     * @param callable $inspector Inspects result, signature is (ResponseInterface $response, string $rId = null)
     * @param array $options
     * @return CreateDatabase
     */
    public static function withInspector(
        string $databaseName,
        callable $inspector,
        array $options = []
    ): CreateDatabase {
        return new self($databaseName, $options, $inspector);
    }

    public function checkResponse(ResponseInterface $response, string $rId = null): ?int
    {
        return ($this->inspector)($response, $rId);
    }

    public function collectionName(): string
    {
        throw new LogicException('Not possible at the moment, see ArangoDB docs');
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
