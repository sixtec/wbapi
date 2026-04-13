<?php

declare(strict_types=1);

namespace Sixtec\WBApi\DTOs;

use Sixtec\WBApi\Domain\ValueObjects\MessageId;

/**
 * @author Mário Lucas
 * @since  2026-04-12
 */
final class MessageResponseDTO
{
    public function __construct(
        public readonly MessageId $messageId,
        public readonly string $to,
        public readonly string $status,
    ) {
    }
}
