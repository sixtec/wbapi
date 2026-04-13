<?php

declare(strict_types=1);

namespace Sixtec\WBApi\Mappers;

use Sixtec\WBApi\DTOs\SendTextMessageDTO;

/**
 * @author Mário Lucas
 * @since  2026-04-12
 */
final class TextMessageMapper
{
    /**
     * @return array<string, mixed>
     */
    public function toPayload(SendTextMessageDTO $dto): array
    {
        return [
            'messaging_product' => 'whatsapp',
            'recipient_type'    => 'individual',
            'to'                => $dto->to->getValue(),
            'type'              => 'text',
            'text'              => [
                'preview_url' => $dto->previewUrl,
                'body'        => $dto->body,
            ],
        ];
    }
}
