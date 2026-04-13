<?php

declare(strict_types=1);

namespace Sixtec\WBApi\Domain\Entities;

use Sixtec\WBApi\Domain\ValueObjects\PhoneNumber;

/**
 * @author Mário Lucas
 * @since  2026-04-12
 */
final class Contact
{
    public function __construct(
        private readonly PhoneNumber $phone,
        private readonly ?string $name = null,
        private readonly ?string $waId = null,
    ) {
    }

    public function getPhone(): PhoneNumber
    {
        return $this->phone;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getWaId(): ?string
    {
        return $this->waId;
    }
}
