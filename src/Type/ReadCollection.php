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

use ArangoDBClient\HttpHelper;
use ArangoDBClient\Urls;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class ReadCollection implements Type, HasResponse
{
    use ToHttpTrait;

    /**
     * @var string
     */
    private $collectionName;

    /**
     * @var string
     */
    private $result = '{}';

    /**
     * Inspects response
     *
     * @var callable
     */
    private $inspector;

    private function __construct(
        string $collectionName,
        callable $inspector = null
    ) {
        $this->collectionName = $collectionName;
        $this->inspector = $inspector ?: function (ResponseInterface $response, string $rId = null) {
            return null;
        };
    }

    /**
     * @see https://docs.arangodb.com/3.2/HTTP/Collection/Getting.html#return-information-about-a-collection
     * @see https://docs.arangodb.com/3.2/Manual/DataModeling/Collections/#collection
     *
     * @param string $collectionName
     * @param array $options
     * @return ReadCollection
     */
    public static function with(string $collectionName, array $options = []): ReadCollection
    {
        return new self($collectionName, $options);
    }

    /**
     * @see https://docs.arangodb.com/3.2/HTTP/Collection/Getting.html#return-information-about-a-collection
     * @see https://docs.arangodb.com/3.2/Manual/DataModeling/Collections/#collection
     *
     * @param string $collectionName
     * @param callable $inspector Inspects result, signature is (ResponseInterface $response, string $rId = null)
     * @param array $options
     * @return ReadCollection
     */
    public static function withInspector(
        string $collectionName,
        callable $inspector,
        array $options = []
    ): ReadCollection {
        return new self($collectionName, $options, $inspector);
    }

    public function checkResponse(ResponseInterface $response, string $rId = null): ?int
    {
        $this->result = $response->getBody();

        return ($this->inspector)($response, $rId);
    }

    public function collectionName(): string
    {
        return $this->collectionName;
    }

    public function toRequest(): RequestInterface
    {
        return new Request(
            HttpHelper::METHOD_POST,
            Urls::URL_COLLECTION
        );
    }

    public function toJs(): string
    {
        return 'var rId = db._collection("' . $this->collectionName . '");';
    }

    public function rawResult(): ?string
    {
        return $this->result === '{}' ? null : $this->result;
    }

    public function result()
    {
        return json_decode($this->result, true)['result'] ?? null;
    }
}
