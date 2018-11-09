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

use ArangoDb\Url;
use Fig\Http\Message\RequestMethodInterface;
use ArangoDb\Http\Request;
use Psr\Http\Message\RequestInterface;

final class TruncateCollection implements CollectionType
{
    /**
     * @var string
     */
    private $collectionName;

    private function __construct(string $collectionName)
    {
        $this->collectionName = $collectionName;
    }

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/Collection/Creating.html#truncate-collection
     * @see https://docs.arangodb.com/3.3/Manual/DataModeling/Collections/DatabaseMethods.html#truncate
     *
     * @param string $collectionName
     * @return TruncateCollection
     */
    public static function with(string $collectionName): TruncateCollection
    {
        return new self($collectionName);
    }

    public function collectionName(): string
    {
        return $this->collectionName;
    }

    public function toRequest(): RequestInterface
    {
        return new Request(
            RequestMethodInterface::METHOD_PUT,
            Url::COLLECTION . '/' . $this->collectionName . '/truncate'
        );
    }

    public function toJs(): string
    {
        return 'var rId = db._truncate("' . $this->collectionName . '");';
    }
}
