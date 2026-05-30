<?php

declare(strict_types=1);

namespace Sixtec\WBApi\Webhook\Events;

/**
 * @author Mário Lucas
 * @since  2026-04-12
 */
final class MessageTypingEvent
{
    /**
     * @param array<string, mixed>|null $rawData
     */
    public function __construct(
        public readonly string $contactId,
        public readonly string $timestamp,
        public readonly ?string $messageId = null,
        public readonly ?array $rawData = null,
    ) {
    }
}
