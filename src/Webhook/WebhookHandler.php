<?php

declare(strict_types=1);

namespace Sixtec\WBApi\Webhook;

use Sixtec\WBApi\Exceptions\WebhookVerificationException;
use Sixtec\WBApi\Webhook\Events\MessageDeliveredEvent;
use Sixtec\WBApi\Webhook\Events\MessageReadEvent;
use Sixtec\WBApi\Webhook\Events\MessageReceivedEvent;

/**
 * @author Mário Lucas
 * @since  2026-04-12
 */
final class WebhookHandler
{
    private readonly WebhookPayloadParser $parser;

    public function __construct(private readonly string $verifyToken)
    {
        $this->parser = new WebhookPayloadParser();
    }

    /**
     * Handles the GET verification challenge sent by Meta during webhook setup.
     *
     * @throws WebhookVerificationException
     */
    public function verify(string $mode, string $token, string $challenge): string
    {
        if ($mode !== 'subscribe') {
            throw new WebhookVerificationException("Invalid hub.mode: {$mode}");
        }

        if (!hash_equals($this->verifyToken, $token)) {
            throw new WebhookVerificationException('Webhook token verification failed.');
        }

        return $challenge;
    }

    /**
     * Parses the incoming POST payload into typed domain events.
     *
        * @param  array<string, mixed> $payload  Decoded JSON from the webhook POST body
     * @return array<MessageReceivedEvent|MessageDeliveredEvent|MessageReadEvent>
     */
    public function handle(array $payload): array
    {
        if (($payload['object'] ?? '') !== 'whatsapp_business_account') {
            return [];
        }

        return $this->parser->parse($payload);
    }
}
