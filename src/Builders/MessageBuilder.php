<?php

declare(strict_types=1);

namespace Sixtec\WBApi\Builders;

use Sixtec\WBApi\Domain\ValueObjects\MediaUrl;
use Sixtec\WBApi\Domain\ValueObjects\PhoneNumber;
use Sixtec\WBApi\DTOs\MediaType;
use Sixtec\WBApi\DTOs\MessageResponseDTO;
use Sixtec\WBApi\DTOs\SendMediaMessageDTO;
use Sixtec\WBApi\DTOs\SendTemplateMessageDTO;
use Sixtec\WBApi\DTOs\SendTextMessageDTO;
use Sixtec\WBApi\DTOs\TemplateComponentDTO;
use Sixtec\WBApi\Exceptions\WBMetaException;
use Sixtec\WBApi\Services\MessagingService;

/**
 * @author Mário Lucas
 * @since  2026-04-12
 */
final class MessageBuilder
{
    private readonly PhoneNumber $phone;

    private ?string $textBody         = null;
    private bool    $previewUrl       = false;

    private ?string $templateName     = null;
    private string  $templateLanguage = 'pt_BR';
    /**
     * @var array<int, TemplateComponentDTO>
     */
    private array   $templateComponents = [];

    private ?string    $mediaUrl      = null;
    private ?MediaType $mediaType     = null;
    private ?string    $mediaCaption  = null;
    private ?string    $mediaFilename = null;

    public function __construct(
        private readonly MessagingService $service,
        string $phone,
    ) {
        $this->phone = new PhoneNumber($phone);
    }

    public function text(string $body, bool $previewUrl = false): static
    {
        $this->textBody   = $body;
        $this->previewUrl = $previewUrl;
        return $this;
    }

    /**
     * @param TemplateComponentDTO[] $components
     */
    public function template(string $name, string $language = 'pt_BR', array $components = []): static
    {
        $this->templateName       = $name;
        $this->templateLanguage   = $language;
        $this->templateComponents = $components;
        return $this;
    }

    public function image(string $url, ?string $caption = null): static
    {
        $this->mediaUrl     = $url;
        $this->mediaType    = MediaType::Image;
        $this->mediaCaption = $caption;
        return $this;
    }

    public function audio(string $url): static
    {
        $this->mediaUrl  = $url;
        $this->mediaType = MediaType::Audio;
        return $this;
    }

    public function video(string $url, ?string $caption = null): static
    {
        $this->mediaUrl     = $url;
        $this->mediaType    = MediaType::Video;
        $this->mediaCaption = $caption;
        return $this;
    }

    public function document(string $url, ?string $filename = null, ?string $caption = null): static
    {
        $this->mediaUrl      = $url;
        $this->mediaType     = MediaType::Document;
        $this->mediaFilename = $filename;
        $this->mediaCaption  = $caption;
        return $this;
    }

    public function send(): MessageResponseDTO
    {
        return match (true) {
            $this->textBody !== null => $this->service->sendText(
                new SendTextMessageDTO(
                    to:         $this->phone,
                    body:       $this->textBody,
                    previewUrl: $this->previewUrl,
                ),
            ),

            $this->templateName !== null => $this->service->sendTemplate(
                new SendTemplateMessageDTO(
                    to:           $this->phone,
                    templateName: $this->templateName,
                    languageCode: $this->templateLanguage,
                    components:   $this->templateComponents,
                ),
            ),

            $this->mediaUrl !== null && $this->mediaType !== null => $this->service->sendMedia(
                new SendMediaMessageDTO(
                    to:        $this->phone,
                    mediaType: $this->mediaType,
                    url:       new MediaUrl($this->mediaUrl),
                    caption:   $this->mediaCaption,
                    filename:  $this->mediaFilename,
                ),
            ),

            default => throw new WBMetaException(
                'No message type selected. Call text(), template(), image(), audio(), video(), or document() before send().',
            ),
        };
    }
}
