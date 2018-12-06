<?php
/**
 * Sandro Keil (https://sandro-keil.de)
 *
 * @link      http://github.com/sandrokeil/arangodb-php-client for the canonical source repository
 * @copyright Copyright (c) 2018 Sandro Keil
 * @license   http://github.com/sandrokeil/arangodb-php-client/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ArangoDb\Guard;

use ArangoDb\Exception\GuardErrorException;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;

final class SuccessHttpStatusCode implements Guard
{
    use ContentIdTrait;

    public function __invoke(ResponseInterface $response)
    {
        $httpStatusCode = $response->getStatusCode();

        if ($httpStatusCode < StatusCodeInterface::STATUS_OK
            || $httpStatusCode > StatusCodeInterface::STATUS_MULTIPLE_CHOICES
        ) {
            throw GuardErrorException::with($response);
        }
    }
}
