<?php

declare(strict_types=1);

namespace Sixtec\WBApi\Mappers;

trait ContextPayload
{
    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function withContext(array $payload, ?string $messageId): array
    {
        if ($messageId !== null && $messageId !== '') {
            $payload['context'] = ['message_id' => $messageId];
        }

        return $payload;
    }
}
