<?php

declare(strict_types=1);

namespace Sixtec\WBApi;

use Sixtec\WBApi\Builders\MessageBuilder;
use Sixtec\WBApi\Config\WBMetaConfig;
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
    private static ?WBMetaClient $client = null;

    private function __construct()
    {
    }

    public static function configure(WBMetaConfig $config): void
    {
        self::$config = $config;
        self::$messagingService = null;
        self::$client = null;
    }

    /**
     * Bind a custom MessagingService — useful for testing.
     */
    public static function bindService(MessagingService $service): void
    {
        self::$messagingService = $service;
        self::$client = null;
    }

    public static function client(): WBMetaClient
    {
        return self::resolveClient();
    }

    public static function to(string $phone): MessageBuilder
    {
        return self::resolveClient()->to($phone);
    }

    public static function webhook(): WebhookHandler
    {
        return self::resolveClient()->webhook();
    }

    public static function markAsRead(string $messageId): bool
    {
        return self::resolveClient()->markAsRead($messageId);
    }

    /**
     * Resets the facade state — for use in tests only.
     */
    public static function reset(): void
    {
        self::$config = null;
        self::$messagingService = null;
        self::$client = null;
    }

    private static function resolveClient(): WBMetaClient
    {
        if (self::$client !== null) {
            return self::$client;
        }

        $config = self::resolveConfig();

        if (self::$messagingService !== null) {
            self::$client = new WBMetaClient($config, self::$messagingService);

            return self::$client;
        }

        self::$client = WBMetaClient::fromConfig($config);

        return self::$client;
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
