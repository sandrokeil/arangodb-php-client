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

final class TruncateCollection implements Type
{
    use ToHttpTrait;

    /**
     * @var string
     */
    private $collectionName;

    /**
     * Inspects response
     *
     * @var callable
     */
    private $inspector;

    private function __construct(string $collectionName, callable $inspector = null)
    {
        $this->collectionName = $collectionName;
        $this->inspector = $inspector ?: function (ResponseInterface $response, string $rId = null) {
            if ($rId) {
                return null;
            }

            return strpos($response->getBody(), '"error":false') === false ? 422 : null;
        };
    }

    /**
     * @see https://docs.arangodb.com/3.2/HTTP/Collection/Creating.html#truncate-collection
     * @see https://docs.arangodb.com/3.2/Manual/DataModeling/Collections/DatabaseMethods.html#truncate
     *
     * @param string $collectionName
     * @return TruncateCollection
     */
    public static function with(string $collectionName): TruncateCollection
    {
        return new self($collectionName);
    }

    /**
     * @see https://docs.arangodb.com/3.2/HTTP/Collection/Creating.html#truncate-collection
     * @see https://docs.arangodb.com/3.2/Manual/DataModeling/Collections/DatabaseMethods.html#truncate
     *
     * @param string $collectionName
     * @param callable $inspector Inspects result, signature is (ResponseInterface $response, string $rId = null)
     * @return TruncateCollection
     */
    public static function withInspector(
        string $collectionName,
        callable $inspector
    ): TruncateCollection {
        return new self($collectionName, $inspector);
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
            HttpHelper::METHOD_PUT,
            Urls::URL_COLLECTION . '/' . $this->collectionName . '/truncate',
            []
        );
    }

    public function toJs(): string
    {
        return 'var rId = db._truncate("' . $this->collectionName . '");';
    }
}
