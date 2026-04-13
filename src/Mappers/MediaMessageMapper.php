<?php

declare(strict_types=1);

namespace Sixtec\WBApi\Mappers;

use Sixtec\WBApi\DTOs\MediaType;
use Sixtec\WBApi\DTOs\SendMediaMessageDTO;

/**
 * @author Mário Lucas
 * @since  2026-04-12
 */
final class MediaMessageMapper
{
    /**
     * @return array<string, mixed>
     */
    public function toPayload(SendMediaMessageDTO $dto): array
    {
        $media = ['link' => $dto->url->getValue()];

        if ($dto->caption !== null && $dto->mediaType !== MediaType::Audio) {
            $media['caption'] = $dto->caption;
        }

        if ($dto->filename !== null && $dto->mediaType === MediaType::Document) {
            $media['filename'] = $dto->filename;
        }

        $type = $dto->mediaType->value;

        return [
            'messaging_product' => 'whatsapp',
            'recipient_type'    => 'individual',
            'to'                => $dto->to->getValue(),
            'type'              => $type,
            $type               => $media,
        ];
    }
}
