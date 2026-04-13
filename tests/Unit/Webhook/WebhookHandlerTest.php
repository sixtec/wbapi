<?php

declare(strict_types=1);

namespace Sixtec\WBApi\Tests\Unit\Webhook;

use PHPUnit\Framework\TestCase;
use Sixtec\WBApi\Exceptions\WebhookVerificationException;
use Sixtec\WBApi\Webhook\Events\MessageDeliveredEvent;
use Sixtec\WBApi\Webhook\Events\MessageReadEvent;
use Sixtec\WBApi\Webhook\Events\MessageReceivedEvent;
use Sixtec\WBApi\Webhook\WebhookHandler;

final class WebhookHandlerTest extends TestCase
{
    private WebhookHandler $handler;

    protected function setUp(): void
    {
        $this->handler = new WebhookHandler('my-secret-token');
    }

    public function testVerificationReturnsChallenge(): void
    {
        $challenge = $this->handler->verify('subscribe', 'my-secret-token', 'abc123');

        $this->assertSame('abc123', $challenge);
    }

    public function testVerificationFailsOnWrongMode(): void
    {
        $this->expectException(WebhookVerificationException::class);
        $this->expectExceptionMessageMatches('/hub\.mode/');

        $this->handler->verify('unsubscribe', 'my-secret-token', 'abc123');
    }

    public function testVerificationFailsOnWrongToken(): void
    {
        $this->expectException(WebhookVerificationException::class);

        $this->handler->verify('subscribe', 'wrong-token', 'abc123');
    }

    public function testHandleIgnoresNonWhatsAppPayload(): void
    {
        $events = $this->handler->handle(['object' => 'instagram']);

        $this->assertEmpty($events);
    }

    public function testHandleIncomingTextMessage(): void
    {
        $payload = $this->buildMessagePayload([
            'id'        => 'wamid.001',
            'from'      => '5511999999999',
            'type'      => 'text',
            'timestamp' => '1718000000',
            'text'      => ['body' => 'Hello!'],
        ]);

        $events = $this->handler->handle($payload);

        $this->assertCount(1, $events);
        $this->assertInstanceOf(MessageReceivedEvent::class, $events[0]);

        /** @var MessageReceivedEvent $event */
        $event = $events[0];
        $this->assertSame('wamid.001', $event->messageId);
        $this->assertSame('5511999999999', $event->from);
        $this->assertSame('text', $event->type);
        $this->assertSame('Hello!', $event->textBody);
        $this->assertNull($event->mediaData);
    }

    public function testHandleIncomingImageMessage(): void
    {
        $payload = $this->buildMessagePayload([
            'id'        => 'wamid.002',
            'from'      => '5511999999999',
            'type'      => 'image',
            'timestamp' => '1718000001',
            'image'     => ['mime_type' => 'image/jpeg', 'id' => 'media_id_123'],
        ]);

        $events = $this->handler->handle($payload);

        $this->assertCount(1, $events);
        $this->assertInstanceOf(MessageReceivedEvent::class, $events[0]);

        /** @var MessageReceivedEvent $event */
        $event = $events[0];
        $this->assertSame('image', $event->type);
        $this->assertNull($event->textBody);
        $this->assertSame('media_id_123', $event->mediaData['id']);
    }

    public function testHandleDeliveredStatus(): void
    {
        $payload = $this->buildStatusPayload([
            'id'           => 'wamid.003',
            'status'       => 'delivered',
            'timestamp'    => '1718000002',
            'recipient_id' => '5511999999999',
        ]);

        $events = $this->handler->handle($payload);

        $this->assertCount(1, $events);
        $this->assertInstanceOf(MessageDeliveredEvent::class, $events[0]);

        /** @var MessageDeliveredEvent $event */
        $event = $events[0];
        $this->assertSame('wamid.003', $event->messageId);
        $this->assertSame('5511999999999', $event->recipientId);
    }

    public function testHandleReadStatus(): void
    {
        $payload = $this->buildStatusPayload([
            'id'           => 'wamid.004',
            'status'       => 'read',
            'timestamp'    => '1718000003',
            'recipient_id' => '5511999999999',
        ]);

        $events = $this->handler->handle($payload);

        $this->assertCount(1, $events);
        $this->assertInstanceOf(MessageReadEvent::class, $events[0]);
    }

    public function testHandleMixedMessagesAndStatuses(): void
    {
        $payload = [
            'object' => 'whatsapp_business_account',
            'entry'  => [[
                'id'      => 'WABA_ID',
                'changes' => [[
                    'field' => 'messages',
                    'value' => [
                        'messaging_product' => 'whatsapp',
                        'metadata'          => [],
                        'messages'          => [[
                            'id' => 'wamid.m01', 'from' => '5511111111111',
                            'type' => 'text', 'timestamp' => '1718000010',
                            'text' => ['body' => 'Hi'],
                        ]],
                        'statuses' => [[
                            'id' => 'wamid.s01', 'status' => 'delivered',
                            'timestamp' => '1718000011', 'recipient_id' => '5511111111111',
                        ]],
                    ],
                ]],
            ]],
        ];

        $events = $this->handler->handle($payload);

        $this->assertCount(2, $events);
        $this->assertInstanceOf(MessageReceivedEvent::class, $events[0]);
        $this->assertInstanceOf(MessageDeliveredEvent::class, $events[1]);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /**
     * @param array<string, mixed> $message
     * @return array<string, mixed>
     */
    private function buildMessagePayload(array $message): array
    {
        return [
            'object' => 'whatsapp_business_account',
            'entry'  => [[
                'id'      => 'WABA_ID',
                'changes' => [[
                    'field' => 'messages',
                    'value' => [
                        'messaging_product' => 'whatsapp',
                        'metadata'          => ['display_phone_number' => '15550001234', 'phone_number_id' => '123'],
                        'messages'          => [$message],
                    ],
                ]],
            ]],
        ];
    }

    /**
     * @param array<string, mixed> $status
     * @return array<string, mixed>
     */
    private function buildStatusPayload(array $status): array
    {
        return [
            'object' => 'whatsapp_business_account',
            'entry'  => [[
                'id'      => 'WABA_ID',
                'changes' => [[
                    'field' => 'messages',
                    'value' => [
                        'messaging_product' => 'whatsapp',
                        'metadata'          => ['display_phone_number' => '15550001234', 'phone_number_id' => '123'],
                        'statuses'          => [$status],
                    ],
                ]],
            ]],
        ];
    }
}
