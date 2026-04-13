<?php

declare(strict_types=1);

namespace Sixtec\WBApi\Contracts;

use Sixtec\WBApi\Http\HttpResponse;

/**
 * @author Mário Lucas
 * @since  2026-04-12
 */
interface HttpClientInterface
{
    /**
     * @param array<string, mixed> $payload
     * @param array<string, string> $headers
     */
    public function post(string $url, array $payload, array $headers = []): HttpResponse;

    /**
     * @param array<string, scalar> $query
     * @param array<string, string> $headers
     */
    public function get(string $url, array $query = [], array $headers = []): HttpResponse;
}
