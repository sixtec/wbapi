<?php

declare(strict_types=1);

namespace Sixtec\WBApi\Domain\Entities;

/**
 * @author Mário Lucas
 * @since  2026-04-12
 */
final class Conversation
{
    public function __construct(
        private readonly string $id,
        private readonly string $origin,
        private readonly ?\DateTimeImmutable $expiresAt = null,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getOrigin(): string
    {
        return $this->origin;
    }

    public function getExpiresAt(): ?\DateTimeImmutable
    {
        return $this->expiresAt;
    }
}
