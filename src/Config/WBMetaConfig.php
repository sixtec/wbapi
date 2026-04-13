<?php

declare(strict_types=1);

namespace Sixtec\WBApi\Config;

/**
 * @author Mário Lucas
 * @since  2026-04-12
 */
final class WBMetaConfig
{
    public function __construct(
        public readonly string $accessToken,
        public readonly string $phoneNumberId,
        public readonly string $apiVersion = 'v19.0',
        public readonly string $webhookVerifyToken = '',
        public readonly int $retryAttempts = 3,
        public readonly float $timeout = 30.0,
    ) {
    }

    public function getBaseUrl(): string
    {
        return "https://graph.facebook.com/{$this->apiVersion}";
    }

    public function getMessagesUrl(): string
    {
        return "{$this->getBaseUrl()}/{$this->phoneNumberId}/messages";
    }
}
