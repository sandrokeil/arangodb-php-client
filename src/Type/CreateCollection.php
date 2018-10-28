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

use ArangoDb\VpackStream;
use ArangoDBClient\Urls;
use Fig\Http\Message\RequestMethodInterface;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;

final class CreateCollection implements CollectionType
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
     * @see https://docs.arangodb.com/3.3/HTTP/Collection/Creating.html#create-collection
     * @see https://docs.arangodb.com/3.3/Manual/DataModeling/Collections/DatabaseMethods.html#create
     *
     * @param string $collectionName
     * @param array $options
     * @return CreateCollection
     */
    public static function with(string $collectionName, array $options = []): CreateCollection
    {
        return new self($collectionName, $options);
    }

    public function collectionName(): string
    {
        return $this->name;
    }

    public function toRequest(): RequestInterface
    {
        $options = $this->options;
        $options['name'] = $this->name;

        return new Request(
            RequestMethodInterface::METHOD_POST,
            Urls::URL_COLLECTION,
            [],
            new VpackStream($options)
        );
    }

    public function toJs(): string
    {
        return 'var rId = db._create("' . $this->name . '", ' . json_encode($this->options) . ');';
    }
}
