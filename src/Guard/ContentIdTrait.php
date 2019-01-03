<?php
/**
 * Sandro Keil (https://sandro-keil.de)
 *
 * @link      http://github.com/sandrokeil/arangodb-php-client for the canonical source repository
 * @copyright Copyright (c) 2018-2019 Sandro Keil
 * @license   http://github.com/sandrokeil/arangodb-php-client/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace ArangoDb\Guard;

trait ContentIdTrait
{
    /**
     * Content id
     *
     * @var string|null
     */
    private $contentId;

    private function __construct(?string $contentId)
    {
        $this->contentId = $contentId;
    }

    public static function withContentId(string $contentId = null): self
    {
        if (null === $contentId) {
            $contentId = bin2hex(random_bytes(4));
        }

        return new self($contentId);
    }

    public static function withoutContentId(): self
    {
        return new self(null);
    }

    public function contentId(): ?string
    {
        return $this->contentId;
    }
}
