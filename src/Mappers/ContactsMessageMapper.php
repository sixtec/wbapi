<?php

declare(strict_types=1);

namespace Sixtec\WBApi\Mappers;

use Sixtec\WBApi\DTOs\SendContactsMessageDTO;

final class ContactsMessageMapper
{
    use ContextPayload;

    /**
     * @return array<string, mixed>
     */
    public function toPayload(SendContactsMessageDTO $dto): array
    {
        return $this->withContext([
            'messaging_product' => 'whatsapp',
            'recipient_type'    => 'individual',
            'to'                => $dto->to->getValue(),
            'type'              => 'contacts',
            'contacts'          => $dto->contacts,
        ], $dto->contextMessageId);
    }
}
