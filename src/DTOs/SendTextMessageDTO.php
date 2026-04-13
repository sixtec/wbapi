<?php

declare(strict_types=1);

namespace Sixtec\WBApi\DTOs;

use Sixtec\WBApi\Domain\ValueObjects\PhoneNumber;

/**
 * @author Mário Lucas
 * @since  2026-04-12
 */
final class SendTextMessageDTO
{
    public function __construct(
        public readonly PhoneNumber $to,
        public readonly string $body,
        public readonly bool $previewUrl = false,
    ) {
    }
}
