<?php

declare(strict_types=1);

namespace Sixtec\WBApi\Auth;

use Sixtec\WBApi\Contracts\TokenStorageInterface;

/**
 * @author Mário Lucas
 * @since  2026-04-12
 */
final class InMemoryTokenStorage implements TokenStorageInterface
{
    /**
     * @var array<string, array{value: string, expires: int|null}>
     */
    private array $store = [];

    public function get(string $key): ?string
    {
        if (!isset($this->store[$key])) {
            return null;
        }

        ['value' => $value, 'expires' => $expires] = $this->store[$key];

        if ($expires !== null && $expires < time()) {
            $this->forget($key);
            return null;
        }

        return $value;
    }

    public function set(string $key, string $value, ?int $ttl = null): void
    {
        $this->store[$key] = [
            'value'   => $value,
            'expires' => $ttl !== null ? time() + $ttl : null,
        ];
    }

    public function forget(string $key): void
    {
        unset($this->store[$key]);
    }
}
