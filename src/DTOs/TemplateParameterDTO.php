<?php

declare(strict_types=1);

namespace Sixtec\WBApi\DTOs;

/**
 * @author Mário Lucas
 * @since  2026-04-12
 */
final class TemplateParameterDTO
{
    public function __construct(
        public readonly string $type,   // text | image | video | document
        public readonly string $value,
    ) {
    }
}
