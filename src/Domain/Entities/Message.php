<?php

declare(strict_types=1);

namespace Sixtec\WBApi\Domain\Entities;

use Sixtec\WBApi\Domain\ValueObjects\MessageId;
use Sixtec\WBApi\Domain\ValueObjects\PhoneNumber;

/**
 * @author Mário Lucas
 * @since  2026-04-12
 */
final class Message
{
    public function __construct(
        private readonly MessageId $id,
        private readonly PhoneNumber $to,
        private readonly string $type,
        private readonly \DateTimeImmutable $createdAt = new \DateTimeImmutable(),
    ) {
    }

    public function getId(): MessageId
    {
        return $this->id;
    }

    public function getTo(): PhoneNumber
    {
        return $this->to;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
