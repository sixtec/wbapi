<?php

declare(strict_types=1);

namespace Sixtec\WBApi\Auth;

use Sixtec\WBApi\Contracts\TokenStorageInterface;

/**
 * @author Mário Lucas
 * @since  2026-04-12
 */
final class TokenManager
{
    private const TOKEN_KEY = 'wbmeta_access_token';

    public function __construct(private readonly TokenStorageInterface $storage)
    {
    }

    public function getToken(): ?string
    {
        return $this->storage->get(self::TOKEN_KEY);
    }

    public function setToken(string $token, ?int $ttl = null): void
    {
        $this->storage->set(self::TOKEN_KEY, $token, $ttl);
    }

    public function revokeToken(): void
    {
        $this->storage->forget(self::TOKEN_KEY);
    }

    public function hasToken(): bool
    {
        return $this->getToken() !== null;
    }
}
