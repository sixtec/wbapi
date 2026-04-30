<?php

declare(strict_types=1);

namespace Sixtec\WBApi\Mappers;

use Sixtec\WBApi\DTOs\SendLocationMessageDTO;

final class LocationMessageMapper
{
    use ContextPayload;

    /**
     * @return array<string, mixed>
     */
    public function toPayload(SendLocationMessageDTO $dto): array
    {
        $location = [
            'latitude'  => $dto->latitude,
            'longitude' => $dto->longitude,
        ];

        if ($dto->name !== null) {
            $location['name'] = $dto->name;
        }

        if ($dto->address !== null) {
            $location['address'] = $dto->address;
        }

        return $this->withContext([
            'messaging_product' => 'whatsapp',
            'recipient_type'    => 'individual',
            'to'                => $dto->to->getValue(),
            'type'              => 'location',
            'location'          => $location,
        ], $dto->contextMessageId);
    }
}
