<?php

declare(strict_types=1);

namespace Sixtec\WBApi\Domain\Entities;

/**
 * @author Mário Lucas
 * @since  2026-04-12
 */
final class Template
{
    /**
     * @param array<int, array<string, mixed>> $components
     */
    public function __construct(
        private readonly string $name,
        private readonly string $language,
        private readonly array $components = [],
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getComponents(): array
    {
        return $this->components;
    }
}
