<?php

declare(strict_types=1);

namespace Sixtec\WBApi;

use Sixtec\WBApi\Builders\MessageBuilder;
use Sixtec\WBApi\Config\WBMetaConfig;
use Sixtec\WBApi\Contracts\HttpClientInterface;
use Sixtec\WBApi\DTOs\MarkMessageAsReadDTO;
use Sixtec\WBApi\Http\GuzzleHttpClient;
use Sixtec\WBApi\Services\MessagingService;
use Sixtec\WBApi\Webhook\WebhookHandler;

final class WBMetaClient
{
    public function __construct(
        private readonly WBMetaConfig $config,
        private readonly MessagingService $messagingService,
    ) {
    }

    public static function fromConfig(WBMetaConfig $config, ?HttpClientInterface $httpClient = null): self
    {
        return new self(
            config: $config,
            messagingService: new MessagingService(
                httpClient: $httpClient ?? new GuzzleHttpClient($config),
                config: $config,
            ),
        );
    }

    public function to(string $phone): MessageBuilder
    {
        return new MessageBuilder($this->messagingService, $phone);
    }

    public function webhook(): WebhookHandler
    {
        return new WebhookHandler($this->config->webhookVerifyToken);
    }

    public function markAsRead(string $messageId): bool
    {
        return $this->messagingService->markAsRead(new MarkMessageAsReadDTO($messageId));
    }

    public function getConfig(): WBMetaConfig
    {
        return $this->config;
    }

    public function getMessagingService(): MessagingService
    {
        return $this->messagingService;
    }
}
