<?php

declare(strict_types=1);

namespace Sixtec\WBApi\Domain\ValueObjects;

use Sixtec\WBApi\Exceptions\WBMetaException;

/**
 * @author Mário Lucas
 * @since  2026-04-12
 */
final class MediaUrl
{
    private readonly string $value;

    public function __construct(string $url)
    {
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            throw new WBMetaException("Invalid media URL: {$url}");
        }

        $this->value = $url;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
