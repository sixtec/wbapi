<?php

declare(strict_types=1);

namespace Sixtec\WBApi\Http;

/**
 * @author Mário Lucas
 * @since  2026-04-12
 */
final class HttpResponse
{
    /**
     * @param array<string, mixed> $body
     * @param array<string, array<int, string>> $headers
     */
    public function __construct(
        private readonly int $statusCode,
        private readonly array $body,
        private readonly array $headers = [],
    ) {
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @return array<string, mixed>
     */
    public function getBody(): array
    {
        return $this->body;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function isSuccessful(): bool
    {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $default;
    }
}
