<?php

declare(strict_types=1);

namespace Sixtec\WBApi\DTOs;

use Sixtec\WBApi\Domain\ValueObjects\PhoneNumber;

final class SendInteractiveMessageDTO
{
    /**
     * @param array<string, mixed> $interactive
     */
    public function __construct(
        public readonly PhoneNumber $to,
        public readonly array $interactive,
        public readonly ?string $contextMessageId = null,
    ) {
    }
}
