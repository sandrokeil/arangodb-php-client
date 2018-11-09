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

use ArangoDb\Http\VpackStream;
use ArangoDb\Url;
use Fig\Http\Message\RequestMethodInterface;
use ArangoDb\Http\Request;
use Psr\Http\Message\RequestInterface;

final class CreateIndex implements CollectionType
{
    /**
     * @var string
     */
    private $collectionName;

    /**
     * @var array
     */
    private $options;

    private function __construct(string $collectionName, array $options)
    {
        $this->collectionName = $collectionName;
        $this->options = $options;
    }

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/Indexes/WorkingWith.html#create-index
     * @see https://docs.arangodb.com/3.3/Manual/Indexing/WorkingWithIndexes.html#creating-an-index
     *
     * @param string $collectionName
     * @param array $options
     * @return CreateIndex
     */
    public static function with(string $collectionName, array $options = []): CreateIndex
    {
        return new self($collectionName, $options);
    }

    public function collectionName(): string
    {
        return $this->collectionName;
    }

    public function toRequest(): RequestInterface
    {
        return new Request(
            RequestMethodInterface::METHOD_POST,
            Url::INDEX . '?' . http_build_query(['collection' => $this->collectionName]),
            [],
            new VpackStream($this->options)
        );
    }

    public function toJs(): string
    {
        return 'var rId = db.' . $this->collectionName . '.ensureIndex(' . json_encode($this->options) . ');';
    }
}
