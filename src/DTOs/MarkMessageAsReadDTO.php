<?php

declare(strict_types=1);

namespace Sixtec\WBApi\DTOs;

final class MarkMessageAsReadDTO
{
    public function __construct(public readonly string $messageId)
    {
    }
}
