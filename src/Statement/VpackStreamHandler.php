<?php
/**
 * Sandro Keil (https://sandro-keil.de)
 *
 * @link      http://github.com/sandrokeil/arangodb-php-client for the canonical source repository
 * @copyright Copyright (c) 2018-2019 Sandro Keil
 * @license   http://github.com/sandrokeil/arangodb-php-client/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ArangoDb\Statement;

use Psr\Http\Message\StreamInterface;
use Velocypack\Vpack;

/**
 * ArangoDB Velocypack stream handler
 *
 * @see https://github.com/martin-schilling/php-velocypack
 */
class VpackStreamHandler implements StreamHandler
{
    use ArrayAccessStreamHandlerTrait;

    public const RESULT_TYPE_ARRAY = 0;
    public const RESULT_TYPE_JSON = 1;
    public const RESULT_TYPE_BINARY = 2;
    public const RESULT_TYPE_OBJECT = 3;

    /**
     * @var Vpack[]
     */
    private $data = [];

    /**
     * @var int
     */
    private $resultType;


    public function __construct(StreamInterface $stream, int $resultType)
    {
        $this->data[$this->fetches] = Vpack::fromBinary($stream->getContents());
        $this->length = count($this->data[$this->fetches]['result']);
        $this->batchSize = $this->length;
        $this->resultType = $resultType;
    }

    /**
     * @return array|object|string
     */
    public function current()
    {
        return $this->toType(
            $this->data[$this->fetches]['result'][$this->position - ($this->batchSize * $this->fetches)]
        );
    }

    /**
     * @return array|object|string
     */
    public function result()
    {
        return $this->toType($this->data[$this->fetches]['result']);
    }

    public function raw(): array
    {
        return $this->toType($this->data[$this->fetches]);
    }

    public function completeResult()
    {
        $data = $this->resultType === self::RESULT_TYPE_ARRAY ? [[]] : [];

        $i = 0;

        foreach ($this->data as $vpack) {
            switch ($this->resultType) {
                case self::RESULT_TYPE_ARRAY:
                    $data[] = $vpack['result']->toArray();
                    break;
                case self::RESULT_TYPE_JSON:
                default:
//                    $data .= $i === 0 ? '' : ',';
//                    $data .= $vpack['result']->toJson();
                    $value = $vpack['result']->toJson();
                    $value = rtrim($value, ']');
                    $value = ltrim($value, '[');
                    $data[] = $value;
                    break;
            }
            $i++;
        }
        if ($this->resultType === self::RESULT_TYPE_JSON) {
            $data = implode(',', $data);

            if ($i > 0) {
                return '[' . $data . ']';
            }
            return $data;
        }

        return  array_merge(...$data);

        // TODO needs append method
//        $vpack = new Vpack();
//        foreach ($this->data as $result) {
//            $vpack->append($result['result']);
//        }
//        return $this->toType($vpack);
    }

    public function appendStream(StreamInterface $stream): void
    {
        $this->data[++$this->fetches] = Vpack::fromBinary($stream->getContents());
        $this->length += count($this->data[$this->fetches]['result']);
    }

    /**
     * @param Vpack $vpack
     * @return array|object|string
     */
    private function toType(Vpack $vpack)
    {
        switch ($this->resultType) {
            case self::RESULT_TYPE_OBJECT:
                return (object)$vpack->toArray();
            case self::RESULT_TYPE_ARRAY:
                return $vpack->toArray();
            case self::RESULT_TYPE_BINARY:
                return $vpack->toBinary();
            case self::RESULT_TYPE_JSON:
            default:
                return $vpack->toJson();
        }
    }
}
