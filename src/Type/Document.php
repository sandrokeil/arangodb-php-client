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
use ArangoDb\Guard\Guard;
use ArangoDb\Http\VpackStream;
use ArangoDb\Url;
use Fig\Http\Message\RequestMethodInterface;
use ArangoDb\Http\Request;
use Psr\Http\Message\RequestInterface;

final class Document implements DocumentType, Transactional
{
    /**
     * @var string|null
     */
    private $collectionName;

    /**
     * @var string|null
     */
    private $id;

    /**
     * @var array
     */
    private $options;

    /**
     * @var array
     */
    private $data;

    /**
     * @var string
     */
    private $uri;

    /**
     * @var string
     */
    private $method;

    /**
     * Guard
     *
     * @var Guard
     */
    private $guard;

    /**
     * @param null|string $collectionName
     * @param null|string $id
     * @param string $uri
     * @param string $method
     * @param array $options
     * @param array $docs
     */
    private function __construct(
        ?string $collectionName,
        ?string $id,
        string $uri = '',
        string $method = RequestMethodInterface::METHOD_GET,
        array $options = [],
        array $docs = []
    ) {
        $this->collectionName = $collectionName;
        $this->id = $id;
        $this->uri = $uri;
        $this->method = $method;
        $this->options = $options;
        $this->data = $docs;
    }

    public static function read(string $id): DocumentType
    {
        return new self(
            null,
            $id,
            Url::DOCUMENT . '/' . $id
        );
    }

    public static function readHeader(string $id): DocumentType
    {
        return new self(
            null,
            $id,
            Url::DOCUMENT . '/' . $id,
            RequestMethodInterface::METHOD_HEAD
        );
    }

    public static function create(
        string $collectionName,
        array $docs,
        int $flags = 0
    ): DocumentType {
        $options = [];

        if (0 !== ($flags & self::FLAG_RETURN_NEW)) {
            $options['returnNew'] = true;
        }
        if (0 !== ($flags & self::FLAG_WAIT_FOR_SYNC)) {
            $options['waitForSync'] = true;
        }
        if (0 !== ($flags & self::FLAG_SILENT)) {
            $options['silent'] = true;
        }

        return new self(
            $collectionName,
            null,
            Url::DOCUMENT . '/' . $collectionName,
            RequestMethodInterface::METHOD_POST,
            $options,
            $docs
        );
    }

    public static function delete(
        string $collectionName,
        array $keys,
        int $flags = 0
    ): DocumentType {
        $options = [];

        if (0 !== ($flags & self::FLAG_RETURN_OLD)) {
            $options['returnOld'] = true;
        }
        if (0 !== ($flags & self::FLAG_WAIT_FOR_SYNC)) {
            $options['waitForSync'] = true;
        }
        if (0 !== ($flags & self::FLAG_CHECK_REVS)) {
            $options['ignoreRevs'] = false;
        }

        return new self(
            $collectionName,
            null,
            Url::DOCUMENT . '/' . $collectionName,
            RequestMethodInterface::METHOD_DELETE,
            $options,
            $keys
        );
    }

    public static function deleteOne(
        string $id,
        int $flags = 0
    ): DocumentType {
        $options = [];

        if (0 !== ($flags & self::FLAG_RETURN_OLD)) {
            $options['returnOld'] = true;
        }
        if (0 !== ($flags & self::FLAG_WAIT_FOR_SYNC)) {
            $options['waitForSync'] = true;
        }
        if (0 !== ($flags & self::FLAG_SILENT)) {
            $options['silent'] = true;
        }
        return new self(null, $id, Url::DOCUMENT . '/' . $id, RequestMethodInterface::METHOD_DELETE, $options);
    }

    public static function update(
        string $collectionName,
        array $data,
        int $flags = 0
    ): DocumentType {
        $options = [];

        if (0 !== ($flags & self::FLAG_RETURN_OLD)) {
            $options['returnOld'] = true;
        }
        if (0 !== ($flags & self::FLAG_RETURN_NEW)) {
            $options['returnNew'] = true;
        }
        if (0 !== ($flags & self::FLAG_WAIT_FOR_SYNC)) {
            $options['waitForSync'] = true;
        }
        if (0 !== ($flags & self::FLAG_REPLACE_OBJECTS)) {
            $options['mergeObjects'] = false;
        }
        if (0 !== ($flags & self::FLAG_SILENT)) {
            $options['silent'] = true;
        }
        if (0 !== ($flags & self::FLAG_REMOVE_NULL)) {
            $options['keepNull'] = false;
        }

        return new self(
            $collectionName,
            null,
            Url::DOCUMENT . '/' . $collectionName,
            RequestMethodInterface::METHOD_PATCH,
            $options,
            $data
        );
    }

    public static function updateOne(
        string $id,
        array $data,
        int $flags = 0
    ): DocumentType {
        $options = [];

        if (0 !== ($flags & self::FLAG_RETURN_OLD)) {
            $options['returnOld'] = true;
        }
        if (0 !== ($flags & self::FLAG_RETURN_NEW)) {
            $options['returnNew'] = true;
        }
        if (0 !== ($flags & self::FLAG_WAIT_FOR_SYNC)) {
            $options['waitForSync'] = true;
        }
        if (0 !== ($flags & self::FLAG_REPLACE_OBJECTS)) {
            $options['mergeObjects'] = false;
        }
        if (0 !== ($flags & self::FLAG_SILENT)) {
            $options['silent'] = true;
        }
        if (0 !== ($flags & self::FLAG_REMOVE_NULL)) {
            $options['keepNull'] = false;
        }

        return new self(null, $id, Url::DOCUMENT . '/' . $id, RequestMethodInterface::METHOD_PATCH, $options, $data);
    }

    public static function replace(
        string $collectionName,
        array $data,
        int $flags = 0
    ): DocumentType {
        $options = [];

        if (0 !== ($flags & self::FLAG_RETURN_OLD)) {
            $options['returnOld'] = true;
        }
        if (0 !== ($flags & self::FLAG_RETURN_NEW)) {
            $options['returnNew'] = true;
        }
        if (0 !== ($flags & self::FLAG_WAIT_FOR_SYNC)) {
            $options['waitForSync'] = true;
        }
        if (0 !== ($flags & self::FLAG_SILENT)) {
            $options['silent'] = true;
        }
        if (0 !== ($flags & self::FLAG_CHECK_REVS)) {
            $options['ignoreRevs'] = true;
        }

        return new self(
            $collectionName,
            null,
            Url::DOCUMENT . '/' . $collectionName,
            RequestMethodInterface::METHOD_PUT,
            $options,
            $data
        );
    }

    public static function replaceOne(
        string $id,
        array $data,
        int $flags = 0
    ): DocumentType {
        $options = [];

        if (0 !== ($flags & self::FLAG_RETURN_OLD)) {
            $options['returnOld'] = true;
        }
        if (0 !== ($flags & self::FLAG_RETURN_NEW)) {
            $options['returnNew'] = true;
        }
        if (0 !== ($flags & self::FLAG_WAIT_FOR_SYNC)) {
            $options['waitForSync'] = true;
        }
        if (0 !== ($flags & self::FLAG_SILENT)) {
            $options['silent'] = true;
        }
        if (0 !== ($flags & self::FLAG_CHECK_REVS)) {
            $options['ignoreRevs'] = false;
        }

        return new self(null, $id, Url::DOCUMENT . '/' . $id, RequestMethodInterface::METHOD_PUT, $options, $data);
    }

    public function toRequest(): RequestInterface
    {
        $uri = $this->uri;

        if (! empty($this->options)) {
            $uri .= '?' . http_build_query($this->options);
        }

        if (empty($this->data)) {
            return new Request(
                $this->method,
                $uri
            );
        }

        return new Request(
            $this->method,
            $uri,
            [],
            new VpackStream($this->data)
        );
    }

    public function toJs(): string
    {
        if (null !== $this->collectionName) {
            switch ($this->method) {
                case RequestMethodInterface::METHOD_POST:
                    return 'var rId = db.' . $this->collectionName
                        . '.insert(' . json_encode($this->data) . ', ' . json_encode($this->options) . ');';
                case RequestMethodInterface::METHOD_DELETE:
                    return 'var rId = db.' . $this->collectionName
                        . '.removeByKeys(' . json_encode($this->data) . ');';

                case RequestMethodInterface::METHOD_PUT:
                case RequestMethodInterface::METHOD_PATCH:
                    $function = $this->method === RequestMethodInterface::METHOD_PUT ? 'replace' : 'update';

                    $keys = array_map(function ($doc) {
                        if (isset($doc['_key'])) {
                            return ['_key' => $doc['_key']];
                        }
                        if (isset($doc['_id'])) {
                            return ['_id' => $doc['_id']];
                        }
                        throw new LogicException('Cannot perform document updates due missing _key or _id value.');
                    }, $this->data);

                    return 'var rId = db.' . $this->collectionName
                        . '.' . $function . '(' . json_encode($keys) . ', '
                        . json_encode($this->data) . ', '
                        . json_encode($this->options) . ');';
                    break;
                default:
                    break;
            }
        }

        if (null !== $this->id) {
            switch ($this->method) {
                case RequestMethodInterface::METHOD_PUT:
                    return 'var rId = db._replace("' . $this->id . '", '
                        . json_encode($this->data) . ', '
                        . json_encode($this->options) . ');';
                case RequestMethodInterface::METHOD_DELETE:
                    return 'var rId = db._remove("' . $this->id . '", ' . json_encode($this->options) . ');';
                case RequestMethodInterface::METHOD_PATCH:
                    return 'var rId = db._update("' . $this->id . '", '
                        . json_encode($this->data) . ', '
                        . json_encode($this->options) . ');';
                default:
                    return 'var rId = db._document("' . $this->id . '");';
            }
        }
        throw new LogicException('This operation is not supported');
    }

    public function collectionsRead(): array
    {
        if ($this->method === RequestMethodInterface::METHOD_GET) {
            return [$this->determineCollectionName()];
        }
        return [];
    }

    public function collectionsWrite(): array
    {
        if ($this->method !== RequestMethodInterface::METHOD_GET) {
            return [$this->determineCollectionName()];
        }
        return [];
    }

    private function determineCollectionName(): string
    {
        return $this->collectionName ?: substr($this->id, 0, strpos($this->id, '/'));
    }

    public function useGuard(Guard $guard): Type
    {
        $this->guard = $guard;
        return $this;
    }

    public function guard(): ?Guard
    {
        return $this->guard;
    }
}
