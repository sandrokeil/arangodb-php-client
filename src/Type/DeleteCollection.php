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
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class DeleteCollection implements Type
{
    use ToHttpTrait;

    /**
     * @var string
     */
    private $collectionName;

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
        string $collectionName,
        array $options = [],
        callable $inspector = null
    ) {

        $this->collectionName = $collectionName;
        $this->options = $options;
        $this->inspector = $inspector ?: function (ResponseInterface $response, string $rId = null) {
            if ($rId) {
                return null;
            }

            return strpos($response->getBody(), '"error":false') === false ? 422 : null;
        };
    }

    /**
     * @see https://docs.arangodb.com/3.2/HTTP/Collection/Creating.html#drops-a-collection
     * @see https://docs.arangodb.com/3.2/Manual/DataModeling/Collections/CollectionMethods.html#drop
     *
     * @param string $collectionName
     * @param array $options
     * @return DeleteCollection
     */
    public static function with(string $collectionName, array $options = []): DeleteCollection
    {
        return new self($collectionName, $options);
    }

    /**
     * @see https://docs.arangodb.com/3.2/HTTP/Collection/Creating.html#drops-a-collection
     * @see https://docs.arangodb.com/3.2/Manual/DataModeling/Collections/CollectionMethods.html#drop
     *
     * @param string $collectionName
     * @param callable $inspector Inspects result, signature is (ResponseInterface $response, string $rId = null)
     * @param array $options
     * @return DeleteCollection
     */
    public static function withInspector(
        string $collectionName,
        callable $inspector,
        array $options = []
    ): DeleteCollection {
        return new self($collectionName, $options, $inspector);
    }

    public function checkResponse(ResponseInterface $response, string $rId = null): ?int
    {
        return ($this->inspector)($response, $rId);
    }

    public function collectionName(): string
    {
        return $this->collectionName;
    }

    public function toRequest(): RequestInterface
    {
        return $this->buildAppendBatch(
            HttpHelper::METHOD_DELETE,
            Urls::URL_COLLECTION . '/' . $this->collectionName,
            $this->options
        );
    }

    public function toJs(): string
    {
        return 'var rId = db._drop("' . $this->collectionName . '", '
            . ($this->options ? json_encode($this->options) : '{}') . ');';
    }
}
