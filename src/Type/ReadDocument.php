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

use ArangoDBClient\Urls;
use Fig\Http\Message\RequestMethodInterface;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;

final class ReadDocument implements CollectionType
{
    /**
     * @var string
     */
    private $collectionName;

    /**
     * @var array
     */
    private $id;

    private function __construct(string $collectionName, string $id)
    {
        $this->collectionName = $collectionName;
        $this->id = $id;
    }

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/Document/WorkingWithDocuments.html#read-document
     * @see https://docs.arangodb.com/3.3/Manual/DataModeling/Documents/DocumentMethods.html#document
     *
     * @param string $collectionName
     * @param string $id
     * @return ReadDocument
     */
    public static function with(string $collectionName, string $id): ReadDocument
    {
        return new self($collectionName, $id);
    }

    public function collectionName(): string
    {
        return $this->collectionName;
    }

    public function toRequest(): RequestInterface
    {
        return new Request(
            RequestMethodInterface::METHOD_GET,
            Urls::URL_DOCUMENT . '/' . $this->collectionName . '/' . $this->id
        );
    }

    public function toJs(): string
    {
        return 'var rId = db.' . $this->collectionName . '.document(' . $this->id . ');';
    }
}
