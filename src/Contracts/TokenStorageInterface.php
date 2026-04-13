<?php

declare(strict_types=1);

namespace Sixtec\WBApi\Contracts;

/**
 * @author Mário Lucas
 * @since  2026-04-12
 */
interface TokenStorageInterface
{
    public function get(string $key): ?string;

    public function set(string $key, string $value, ?int $ttl = null): void;

    public function forget(string $key): void;
}
