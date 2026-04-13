<?php

declare(strict_types=1);

namespace Sixtec\WBApi\Domain\ValueObjects;

use InvalidArgumentException;

/**
 * @author Mário Lucas
 * @since  2026-04-12
 */
final class MessageId
{
    public function __construct(private readonly string $value)
    {
        if (empty($value)) {
            throw new InvalidArgumentException('MessageId cannot be empty.');
        }
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
