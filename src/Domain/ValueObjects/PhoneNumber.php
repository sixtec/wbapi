<?php

declare(strict_types=1);

namespace Sixtec\WBApi\Domain\ValueObjects;

use Sixtec\WBApi\Exceptions\WBMetaException;

/**
 * @author Mário Lucas
 * @since  2026-04-12
 */
final class PhoneNumber
{
    private readonly string $value;

    public function __construct(string $phone)
    {
        $normalized = preg_replace('/\D/', '', $phone);

        if ($normalized === null || strlen($normalized) < 10 || strlen($normalized) > 15) {
            throw new WBMetaException("Invalid phone number: {$phone}");
        }

        $this->value = $normalized;
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
