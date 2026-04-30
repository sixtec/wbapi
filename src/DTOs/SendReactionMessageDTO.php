<?php

declare(strict_types=1);

namespace Sixtec\WBApi\DTOs;

use Sixtec\WBApi\Domain\ValueObjects\PhoneNumber;

final class SendReactionMessageDTO
{
    public function __construct(
        public readonly PhoneNumber $to,
        public readonly string $messageId,
        public readonly string $emoji,
    ) {
    }
}
