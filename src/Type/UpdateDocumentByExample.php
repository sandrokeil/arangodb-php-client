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
use Psr\Http\Message\RequestInterface;

final class UpdateDocumentByExample implements CollectionType
{
    /**
     * @var string
     */
    private $collectionName;

    /**
     * @var array
     */
    private $example;

    /**
     * @var array
     */
    private $data;

    /**
     * @var array
     */
    private $options;

    private function __construct(
        string $collectionName,
        array $example,
        array $data,
        array $options = []
    ) {
        $this->collectionName = $collectionName;
        $this->data = $data;
        $this->example = $example;
        $this->options = $options;
    }

    /**
     * @see https://docs.arangodb.com/3.3/HTTP/Document/WorkingWithDocuments.html#update-documents
     * @see https://docs.arangodb.com/3.3/Manual/DataModeling/Documents/DocumentMethods.html#update-by-example
     *
     * @param string $collectionName
     * @param array $example
     * @param array $data
     * @param array $options
     * @return UpdateDocumentByExample
     */
    public static function with(
        string $collectionName,
        array $example,
        array $data,
        array $options = []
    ): UpdateDocumentByExample {
        return new self($collectionName, $example, $data, $options);
    }

    public function collectionName(): string
    {
        return $this->collectionName;
    }

    public function toRequest(): RequestInterface
    {
        throw new LogicException('Not possible at the moment, see ArangoDB docs');
    }

    public function toJs(): string
    {
        return 'var rId = db.' . $this->collectionName
            . '.updateByExample('
            . json_encode($this->example) . ', '
            . json_encode($this->data) . ', '
            . json_encode($this->options)
            . ');';
    }
}
