<?php

declare(strict_types=1);

namespace Sixtec\WBApi\Mappers;

use Sixtec\WBApi\DTOs\SendTemplateMessageDTO;
use Sixtec\WBApi\DTOs\TemplateComponentDTO;
use Sixtec\WBApi\DTOs\TemplateParameterDTO;

/**
 * @author Mário Lucas
 * @since  2026-04-12
 */
final class TemplateMessageMapper
{
    /**
     * @return array<string, mixed>
     */
    public function toPayload(SendTemplateMessageDTO $dto): array
    {
        return [
            'messaging_product' => 'whatsapp',
            'recipient_type'    => 'individual',
            'to'                => $dto->to->getValue(),
            'type'              => 'template',
            'template'          => [
                'name'       => $dto->templateName,
                'language'   => ['code' => $dto->languageCode],
                'components' => array_map(
                    fn (TemplateComponentDTO $c) => $this->mapComponent($c),
                    $dto->components,
                ),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapComponent(TemplateComponentDTO $component): array
    {
        $data = [
            'type'       => $component->type,
            'parameters' => array_map(
                fn (TemplateParameterDTO $p) => $this->mapParameter($p),
                $component->parameters,
            ),
        ];

        if ($component->type === 'button') {
            $data['sub_type'] = $component->subType;
            $data['index']    = (string) $component->index;
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    private function mapParameter(TemplateParameterDTO $parameter): array
    {
        return match ($parameter->type) {
            'image'    => ['type' => 'image',    'image'    => ['link' => $parameter->value]],
            'video'    => ['type' => 'video',    'video'    => ['link' => $parameter->value]],
            'document' => ['type' => 'document', 'document' => ['link' => $parameter->value]],
            default    => ['type' => 'text',     'text'     => $parameter->value],
        };
    }
}
