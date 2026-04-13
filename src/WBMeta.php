<?php

declare(strict_types=1);

namespace Sixtec\WBApi;

use Sixtec\WBApi\Builders\MessageBuilder;
use Sixtec\WBApi\Config\WBMetaConfig;
use Sixtec\WBApi\Http\GuzzleHttpClient;
use Sixtec\WBApi\Services\MessagingService;
use Sixtec\WBApi\Webhook\WebhookHandler;

/**
 * @author Mário Lucas
 * @since  2026-04-12
 *
 * Static facade — primary entry point for the library.
 *
 * Usage:
 *   WBMeta::configure(new WBMetaConfig(...));
 *   WBMeta::to('+5511999999999')->text('Hello!')->send();
 *   WBMeta::to('+5511999999999')->image('https://...')->send();
 *   WBMeta::webhook()->handle($payload);
 *
 */
final class WBMeta
{
    private static ?WBMetaConfig $config = null;
    private static ?MessagingService $messagingService = null;

    private function __construct()
    {
    }

    public static function configure(WBMetaConfig $config): void
    {
        self::$config = $config;
        self::$messagingService = null;
    }

    /**
     * Bind a custom MessagingService — useful for testing.
     */
    public static function bindService(MessagingService $service): void
    {
        self::$messagingService = $service;
    }

    public static function to(string $phone): MessageBuilder
    {
        return new MessageBuilder(self::resolveMessagingService(), $phone);
    }

    public static function webhook(): WebhookHandler
    {
        return new WebhookHandler(self::resolveConfig()->webhookVerifyToken);
    }

    /**
     * Resets the facade state — for use in tests only.
     */
    public static function reset(): void
    {
        self::$config = null;
        self::$messagingService = null;
    }

    private static function resolveMessagingService(): MessagingService
    {
        if (self::$messagingService !== null) {
            return self::$messagingService;
        }

        $config = self::resolveConfig();

        self::$messagingService = new MessagingService(
            httpClient: new GuzzleHttpClient($config),
            config:     $config,
        );

        return self::$messagingService;
    }

    private static function resolveConfig(): WBMetaConfig
    {
        if (self::$config === null) {
            throw new \RuntimeException(
                'WBMeta is not configured. Call WBMeta::configure(new WBMetaConfig(...)) first.',
            );
        }

        return self::$config;
    }
}
