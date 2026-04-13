<?php

declare(strict_types=1);

namespace Sixtec\WBApi\Tests\Fakes;

use Sixtec\WBApi\Contracts\HttpClientInterface;
use Sixtec\WBApi\Http\HttpResponse;

final class FakeHttpClient implements HttpClientInterface
{
    /**
     * @var list<HttpResponse>
     */
    private array $responses = [];

    /**
     * @var list<array<string, mixed>>
     */
    private array $requests  = [];

    /**
     * @param array<string, mixed> $body
     */
    public function addResponse(int $statusCode, array $body): void
    {
        $this->responses[] = new HttpResponse($statusCode, $body);
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, string> $headers
     */
    public function post(string $url, array $payload, array $headers = []): HttpResponse
    {
        $this->requests[] = ['method' => 'POST', 'url' => $url, 'payload' => $payload];
        return $this->dequeue();
    }

    /**
     * @param array<string, scalar> $query
     * @param array<string, string> $headers
     */
    public function get(string $url, array $query = [], array $headers = []): HttpResponse
    {
        $this->requests[] = ['method' => 'GET', 'url' => $url, 'query' => $query];
        return $this->dequeue();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function getRequests(): array
    {
        return $this->requests;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getLastRequest(): ?array
    {
        return end($this->requests) ?: null;
    }

    private function dequeue(): HttpResponse
    {
        if (empty($this->responses)) {
            return new HttpResponse(200, []);
        }

        return array_shift($this->responses);
    }
}
