<?php

declare(strict_types=1);

namespace Sixtec\WBApi\DTOs;

use Sixtec\WBApi\Domain\ValueObjects\PhoneNumber;

final class SendLocationMessageDTO
{
    public function __construct(
        public readonly PhoneNumber $to,
        public readonly float $latitude,
        public readonly float $longitude,
        public readonly ?string $name = null,
        public readonly ?string $address = null,
        public readonly ?string $contextMessageId = null,
    ) {
    }
}
