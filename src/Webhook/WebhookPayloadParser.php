<?php

declare(strict_types=1);

namespace Sixtec\WBApi\Webhook;

use Sixtec\WBApi\Webhook\Events\MessageDeliveredEvent;
use Sixtec\WBApi\Webhook\Events\MessageReadEvent;
use Sixtec\WBApi\Webhook\Events\MessageReceivedEvent;

/**
 * @author Mário Lucas
 * @since  2026-04-12
 */
final class WebhookPayloadParser
{
    /**
        * @param  array<string, mixed> $payload  Decoded JSON body from Meta webhook POST
     * @return array<MessageReceivedEvent|MessageDeliveredEvent|MessageReadEvent>
     */
    public function parse(array $payload): array
    {
        $events = [];

        foreach ($payload['entry'] ?? [] as $entry) {
            foreach ($entry['changes'] ?? [] as $change) {
                if (($change['field'] ?? '') !== 'messages') {
                    continue;
                }

                $value = $change['value'] ?? [];

                foreach ($value['messages'] ?? [] as $message) {
                    $events[] = $this->parseMessage($message);
                }

                foreach ($value['statuses'] ?? [] as $status) {
                    $events[] = $this->parseStatus($status);
                }
            }
        }

        return $events;
    }

    /**
     * @param array<string, mixed> $message
     */
    private function parseMessage(array $message): MessageReceivedEvent
    {
        $type = $message['type'] ?? 'text';

        return new MessageReceivedEvent(
            messageId: $message['id'],
            from:      $message['from'],
            type:      $type,
            timestamp: $message['timestamp'],
            textBody:  $type === 'text' ? ($message['text']['body'] ?? null) : null,
            mediaData: $type !== 'text' ? ($message[$type] ?? null) : null,
            rawData:   $message,
        );
    }

    /**
     * @param array<string, mixed> $status
     */
    private function parseStatus(array $status): MessageDeliveredEvent|MessageReadEvent
    {
        return match ($status['status'] ?? '') {
            'read' => new MessageReadEvent(
                messageId:   $status['id'],
                recipientId: $status['recipient_id'],
                timestamp:   $status['timestamp'],
            ),
            default => new MessageDeliveredEvent(
                messageId:   $status['id'],
                recipientId: $status['recipient_id'],
                timestamp:   $status['timestamp'],
            ),
        };
    }
}
