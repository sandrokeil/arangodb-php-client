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

final class UpdateDocument implements CollectionType
{
    /**
     * @var string
     */
    private $collectionName;

    /**
     * @var string
     */
    private $id;

    /**
     * @var array
     */
    private $data;

    /**
     * @var array
     */
    private $options;

    private function __construct(string $collectionName, string $id, array $data, array $options = [])
    {
        $this->collectionName = $collectionName;
        $this->data = $data;
        $this->id = $id;
        $this->options = $options;
    }

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/Document/WorkingWithDocuments.html#update-document
     * @see https://docs.arangodb.com/3.3/Manual/DataModeling/Documents/DocumentMethods.html#update
     *
     * @param string $collectionName
     * @param string $id
     * @param array $data
     * @param array $options
     * @return UpdateDocument
     */
    public static function with(string $collectionName, string $id, array $data, array $options = []): UpdateDocument
    {
        return new self($collectionName, $id, $data, $options);
    }

    public function collectionName(): string
    {
        return $this->collectionName;
    }

    public function toRequest(): RequestInterface
    {
        return new Request(
            RequestMethodInterface::METHOD_PATCH,
            Url::DOCUMENT . '/' . $this->collectionName . '/' . $this->id . '?' . http_build_query($this->options),
            [],
            new VpackStream($this->data)
        );
    }

    public function toJs(): string
    {
        return 'var rId = db.' . $this->collectionName
            . '.update("' . $this->id . '", ' . json_encode($this->data) . ', ' . json_encode($this->options) . ');';
    }
}
