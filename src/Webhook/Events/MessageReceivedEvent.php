<?php

declare(strict_types=1);

namespace Sixtec\WBApi\Webhook\Events;

/**
 * @author Mário Lucas
 * @since  2026-04-12
 */
final class MessageReceivedEvent
{
    /**
     * @param array<string, mixed>|null $mediaData
     * @param array<string, mixed>|null $rawData
     */
    public function __construct(
        public readonly string  $messageId,
        public readonly string  $from,
        public readonly string  $type,
        public readonly string  $timestamp,
        public readonly ?string $textBody  = null,
        public readonly ?array  $mediaData = null,
        public readonly ?array  $rawData   = null,
    ) {
    }
}
