<?php

declare(strict_types=1);

namespace Sixtec\WBApi\DTOs;

/**
 * @author Mário Lucas
 * @since  2026-04-12
 */
final class TemplateComponentDTO
{
    /**
     * @param TemplateParameterDTO[] $parameters
     */
    public function __construct(
        public readonly string $type,             // header | body | button
        public readonly array $parameters = [],
        public readonly ?int $index = null,       // required for button type
        public readonly ?string $subType = null,  // quick_reply | url (for button)
    ) {
    }
}
