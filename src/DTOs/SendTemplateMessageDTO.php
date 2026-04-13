<?php

declare(strict_types=1);

namespace Sixtec\WBApi\DTOs;

use Sixtec\WBApi\Domain\ValueObjects\PhoneNumber;

/**
 * @author Mário Lucas
 * @since  2026-04-12
 */
final class SendTemplateMessageDTO
{
    /**
     * @param TemplateComponentDTO[] $components
     */
    public function __construct(
        public readonly PhoneNumber $to,
        public readonly string $templateName,
        public readonly string $languageCode = 'pt_BR',
        public readonly array $components = [],
    ) {
    }
}
