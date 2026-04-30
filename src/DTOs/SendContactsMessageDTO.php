<?php

declare(strict_types=1);

namespace Sixtec\WBApi\DTOs;

use Sixtec\WBApi\Domain\ValueObjects\PhoneNumber;

final class SendContactsMessageDTO
{
    /**
     * @param list<array<string, mixed>> $contacts
     */
    public function __construct(
        public readonly PhoneNumber $to,
        public readonly array $contacts,
        public readonly ?string $contextMessageId = null,
    ) {
    }
}
