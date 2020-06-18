<?php
/**
 * Sandro Keil (https://sandro-keil.de)
 *
 * @link      http://github.com/sandrokeil/arangodb-php-client for the canonical source repository
 * @copyright Copyright (c) 2018-2020 Sandro Keil
 * @license   http://github.com/sandrokeil/arangodb-php-client/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ArangoDb\Handler;

use ArangoDb\Exception\GuardErrorException;
use ArangoDb\Exception\UnexpectedResponse;
use ArangoDb\Guard\SuccessHttpStatusCode;
use ArangoDb\Http\TypeSupport;
use ArangoDb\Type\Document as DocumentType;
use ArangoDb\Util\Json;
use Psr\Http\Message\ResponseInterface;

final class Document implements DocumentHandler
{
    /**
     * @var TypeSupport
     **/
    private $client;

    /**
     * @var SuccessHttpStatusCode
     */
    private static $guard;

    public function __construct(TypeSupport $client)
    {
        $this->client = $client;
        self::$guard = SuccessHttpStatusCode::withoutContentId();
    }

    public function save(string $collectionName, array $docs, int $flags = 0): string
    {
        $type = DocumentType::create($collectionName, $docs, $flags)
            ->useGuard(self::$guard);

        $response = $this->client->sendType($type);

        $data = Json::decode($response->getBody()->getContents());

        if (! isset($data['_id'])) {
            throw UnexpectedResponse::forType($type, $response);
        }

        return $data['_id'];
    }

    public function get(string $documentId): ResponseInterface
    {
        $type = DocumentType::read($documentId)->useGuard(self::$guard);

        return $this->client->sendType($type);
    }

    public function getById(string $collectionName, string $id): ResponseInterface
    {
        return $this->get($collectionName . self::ID_SEPARATOR . $id);
    }

    public function remove(string $documentId): void
    {
        $type = DocumentType::deleteOne($documentId)
            ->useGuard(self::$guard);

        $this->client->sendType($type);
    }

    public function removeById(string $collectionName, string $id): void
    {
        $this->remove($collectionName . self::ID_SEPARATOR . $id);
    }

    public function has(string $documentId): bool
    {
        $type = DocumentType::readHeader($documentId)
            ->useGuard(self::$guard);

        try {
            $this->client->sendType($type);

            return true;
        } catch (GuardErrorException $e) {
            return false;
        }
    }

    public function hasById(string $collectionName, string $id): bool
    {
        return $this->has($collectionName . self::ID_SEPARATOR . $id);
    }
}
