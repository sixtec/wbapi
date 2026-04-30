<?php

declare(strict_types=1);

namespace Sixtec\WBApi\Mappers;

use Sixtec\WBApi\DTOs\SendReactionMessageDTO;

final class ReactionMessageMapper
{
    /**
     * @return array<string, mixed>
     */
    public function toPayload(SendReactionMessageDTO $dto): array
    {
        return [
            'messaging_product' => 'whatsapp',
            'recipient_type'    => 'individual',
            'to'                => $dto->to->getValue(),
            'type'              => 'reaction',
            'reaction'          => [
                'message_id' => $dto->messageId,
                'emoji'      => $dto->emoji,
            ],
        ];
    }
}
