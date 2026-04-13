<?php

declare(strict_types=1);

namespace Sixtec\WBApi\Webhook\Events;

/**
 * @author Mário Lucas
 * @since  2026-04-12
 */
final class MessageReadEvent
{
    public function __construct(
        public readonly string $messageId,
        public readonly string $recipientId,
        public readonly string $timestamp,
    ) {
    }
}
