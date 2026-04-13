<?php

declare(strict_types=1);

namespace Sixtec\WBApi\DTOs;

use Sixtec\WBApi\Domain\ValueObjects\MediaUrl;
use Sixtec\WBApi\Domain\ValueObjects\PhoneNumber;

/**
 * @author Mário Lucas
 * @since  2026-04-12
 */
final class SendMediaMessageDTO
{
    public function __construct(
        public readonly PhoneNumber $to,
        public readonly MediaType $mediaType,
        public readonly MediaUrl $url,
        public readonly ?string $caption = null,
        public readonly ?string $filename = null,
    ) {
    }
}
