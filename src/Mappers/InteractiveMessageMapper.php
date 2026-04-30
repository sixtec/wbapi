<?php

declare(strict_types=1);

namespace Sixtec\WBApi\Mappers;

use Sixtec\WBApi\DTOs\SendInteractiveMessageDTO;

final class InteractiveMessageMapper
{
    use ContextPayload;

    /**
     * @return array<string, mixed>
     */
    public function toPayload(SendInteractiveMessageDTO $dto): array
    {
        return $this->withContext([
            'messaging_product' => 'whatsapp',
            'recipient_type'    => 'individual',
            'to'                => $dto->to->getValue(),
            'type'              => 'interactive',
            'interactive'       => $dto->interactive,
        ], $dto->contextMessageId);
    }
}
