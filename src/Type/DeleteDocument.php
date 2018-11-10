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

final class DeleteDocument implements CollectionType
{
    /**
     * @var string
     */
    private $collectionName;

    /**
     * @var array
     */
    private $keys;

    /**
     * @var array
     */
    private $options;

    private function __construct(string $collectionName, array $keys, array $options = [])
    {
        $this->collectionName = $collectionName;
        $this->keys = $keys;
        $this->options = $options;
    }

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/Document/WorkingWithDocuments.html#removes-multiple-documents
     * @see https://docs.arangodb.com/3.3/Manual/DataModeling/Documents/DocumentMethods.html#remove-by-example
     *
     * @param string $collectionName
     * @param array $keys
     * @param array $options
     * @return DeleteDocument
     */
    public static function with(string $collectionName, array $keys, array $options = []): DeleteDocument
    {
        return new self($collectionName, $keys, $options);
    }

    public function collectionName(): string
    {
        return $this->collectionName;
    }

    public function toRequest(): RequestInterface
    {
        return new Request(
            RequestMethodInterface::METHOD_DELETE,
            Url::DOCUMENT . '/' . $this->collectionName . '?' . http_build_query($this->options),
            [],
            new VpackStream($this->keys)
        );
    }

    public function toJs(): string
    {
        $options = ! empty($this->options['waitForSync']) ? ', true' : ', false';

        if (! empty($this->options['limit'])) {
            $options .= ', ' . (int)$this->options['limit'];
        }

        return 'var rId = db.' . $this->collectionName . '.removeByKeys(' . json_encode($this->keys) . $options . ');';
    }
}
