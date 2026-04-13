<?php

declare(strict_types=1);

namespace Sixtec\WBApi\Http;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Psr\Http\Message\ResponseInterface;
use Sixtec\WBApi\Config\WBMetaConfig;
use Sixtec\WBApi\Contracts\HttpClientInterface;
use Sixtec\WBApi\Exceptions\HttpException;

/**
 * @author Mário Lucas
 * @since  2026-04-12
 */
final class GuzzleHttpClient implements HttpClientInterface
{
    private readonly Client $client;

    public function __construct(private readonly WBMetaConfig $config)
    {
        $this->client = $this->buildClient();
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, string> $headers
     */
    public function post(string $url, array $payload, array $headers = []): HttpResponse
    {
        try {
            $response = $this->client->post($url, [
                'json'    => $payload,
                'headers' => $this->withAuthHeaders($headers),
            ]);

            return $this->parseResponse($response);
        } catch (RequestException $e) {
            throw HttpException::fromRequestException($e);
        }
    }

    /**
     * @param array<string, scalar> $query
     * @param array<string, string> $headers
     */
    public function get(string $url, array $query = [], array $headers = []): HttpResponse
    {
        try {
            $response = $this->client->get($url, [
                'query'   => $query,
                'headers' => $this->withAuthHeaders($headers),
            ]);

            return $this->parseResponse($response);
        } catch (RequestException $e) {
            throw HttpException::fromRequestException($e);
        }
    }

    private function buildClient(): Client
    {
        $stack = HandlerStack::create();
        $stack->push($this->retryMiddleware());

        return new Client([
            'handler'     => $stack,
            'timeout'     => $this->config->timeout,
            'http_errors' => false,
        ]);
    }

    private function retryMiddleware(): callable
    {
        $maxAttempts = $this->config->retryAttempts;

        return Middleware::retry(
            function (int $retries, $request, $response, $exception) use ($maxAttempts): bool {
                if ($retries >= $maxAttempts) {
                    return false;
                }

                if ($exception !== null) {
                    return true;
                }

                if ($response !== null && $response->getStatusCode() >= 500) {
                    return true;
                }

                return false;
            },
            fn (int $retries): int => $retries * 500,
        );
    }

    /**
     * @param array<string, string> $headers
     * @return array<string, string>
     */
    private function withAuthHeaders(array $headers): array
    {
        return array_merge([
            'Authorization' => "Bearer {$this->config->accessToken}",
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
        ], $headers);
    }

    private function parseResponse(ResponseInterface $response): HttpResponse
    {
        $body = json_decode((string) $response->getBody(), true) ?? [];

        return new HttpResponse(
            statusCode: $response->getStatusCode(),
            body: $body,
            headers: $response->getHeaders(),
        );
    }
}
