<?php

declare(strict_types=1);

namespace Sixtec\WBApi\Webhook;

use Sixtec\WBApi\Webhook\Events\MessageDeliveredEvent;
use Sixtec\WBApi\Webhook\Events\MessageReadEvent;
use Sixtec\WBApi\Webhook\Events\MessageReceivedEvent;
use Sixtec\WBApi\Webhook\Events\MessageTypingEvent;

/**
 * @author Mário Lucas
 * @since  2026-04-12
 */
final class WebhookPayloadParser
{
    /**
     * @param  array<string, mixed>  $payload  Decoded JSON body from Meta webhook POST
     * @return array<MessageReceivedEvent|MessageDeliveredEvent|MessageReadEvent|MessageTypingEvent>
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
                    $event = $this->parseStatus($status);

                    if ($event !== null) {
                        $events[] = $event;
                    }
                }
            }
        }

        return $events;
    }

    /**
     * @param array<string, mixed> $message
     */
    private function parseMessage(array $message): MessageReceivedEvent|MessageTypingEvent
    {
        $type = $message['type'] ?? 'text';

        if ($type === 'typing') {
            return new MessageTypingEvent(
                contactId: $message['from'],
                timestamp: $message['timestamp'],
                messageId: $message['id'] ?? null,
                rawData:   $message,
            );
        }

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
    private function parseStatus(array $status): MessageDeliveredEvent|MessageReadEvent|MessageTypingEvent|null
    {
        return match ($status['status'] ?? '') {
            'typing' => new MessageTypingEvent(
                contactId: $status['recipient_id'],
                timestamp: $status['timestamp'],
                messageId: $status['id'] ?? null,
                rawData:   $status,
            ),
            'read' => new MessageReadEvent(
                messageId:   $status['id'],
                recipientId: $status['recipient_id'],
                timestamp:   $status['timestamp'],
            ),
            'delivered' => new MessageDeliveredEvent(
                messageId:   $status['id'],
                recipientId: $status['recipient_id'],
                timestamp:   $status['timestamp'],
            ),
            default => null,
        };
    }
}
