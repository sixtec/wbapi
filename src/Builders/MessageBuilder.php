<?php

declare(strict_types=1);

namespace Sixtec\WBApi\Builders;

use Sixtec\WBApi\Domain\ValueObjects\MediaUrl;
use Sixtec\WBApi\Domain\ValueObjects\PhoneNumber;
use Sixtec\WBApi\DTOs\MediaType;
use Sixtec\WBApi\DTOs\MessageResponseDTO;
use Sixtec\WBApi\DTOs\SendContactsMessageDTO;
use Sixtec\WBApi\DTOs\SendInteractiveMessageDTO;
use Sixtec\WBApi\DTOs\SendLocationMessageDTO;
use Sixtec\WBApi\DTOs\SendMediaMessageDTO;
use Sixtec\WBApi\DTOs\SendReactionMessageDTO;
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

    private ?string $reactionMessageId = null;
    private ?string $reactionEmoji     = null;

    private ?float  $latitude        = null;
    private ?float  $longitude       = null;
    private ?string $locationName    = null;
    private ?string $locationAddress = null;

    /**
     * @var list<array<string, mixed>>
     */
    private array $contacts = [];

    /**
     * @var array<string, mixed>|null
     */
    private ?array $interactive = null;

    private ?string $contextMessageId = null;

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

    public function replyTo(string $messageId): static
    {
        $this->contextMessageId = $messageId;
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

    public function sticker(string $url): static
    {
        $this->mediaUrl  = $url;
        $this->mediaType = MediaType::Sticker;
        return $this;
    }

    public function reaction(string $messageId, string $emoji): static
    {
        $this->reactionMessageId = $messageId;
        $this->reactionEmoji     = $emoji;
        return $this;
    }

    public function removeReaction(string $messageId): static
    {
        return $this->reaction($messageId, '');
    }

    public function location(
        float $latitude,
        float $longitude,
        ?string $name = null,
        ?string $address = null,
    ): static {
        $this->latitude        = $latitude;
        $this->longitude       = $longitude;
        $this->locationName    = $name;
        $this->locationAddress = $address;
        return $this;
    }

    /**
     * @param list<array<string, mixed>> $contacts
     */
    public function contacts(array $contacts): static
    {
        $this->contacts = $contacts;
        return $this;
    }

    public function contact(string $formattedName, string $phone, ?string $firstName = null): static
    {
        $normalizedPhone = new PhoneNumber($phone);

        return $this->contacts([[
            'name'   => [
                'formatted_name' => $formattedName,
                'first_name'     => $firstName ?? $formattedName,
            ],
            'phones' => [[
                'phone' => $normalizedPhone->getValue(),
                'type'  => 'CELL',
            ]],
        ]]);
    }

    /**
     * @param array<string, mixed> $interactive
     */
    public function interactive(array $interactive): static
    {
        $this->interactive = $interactive;
        return $this;
    }

    /**
     * @param list<array{id:string, title:string}> $buttons
     */
    public function buttons(string $body, array $buttons, ?string $footer = null): static
    {
        $interactive = [
            'type'   => 'button',
            'body'   => ['text' => $body],
            'action' => [
                'buttons' => array_map(
                    fn (array $button): array => [
                        'type'  => 'reply',
                        'reply' => [
                            'id'    => $button['id'],
                            'title' => $button['title'],
                        ],
                    ],
                    $buttons,
                ),
            ],
        ];

        if ($footer !== null) {
            $interactive['footer'] = ['text' => $footer];
        }

        return $this->interactive($interactive);
    }

    /**
     * @param list<array{title?:string, rows:list<array{id:string, title:string, description?:string}>}> $sections
     */
    public function list(string $body, string $buttonText, array $sections, ?string $footer = null): static
    {
        $interactive = [
            'type'   => 'list',
            'body'   => ['text' => $body],
            'action' => [
                'button'   => $buttonText,
                'sections' => $sections,
            ],
        ];

        if ($footer !== null) {
            $interactive['footer'] = ['text' => $footer];
        }

        return $this->interactive($interactive);
    }

    public function product(string $body, string $catalogId, string $productRetailerId, ?string $footer = null): static
    {
        $interactive = [
            'type'   => 'product',
            'body'   => ['text' => $body],
            'action' => [
                'catalog_id'          => $catalogId,
                'product_retailer_id' => $productRetailerId,
            ],
        ];

        if ($footer !== null) {
            $interactive['footer'] = ['text' => $footer];
        }

        return $this->interactive($interactive);
    }

    /**
     * @param list<array{title?:string, product_items:list<array{product_retailer_id:string}>}> $sections
     */
    public function productList(string $body, string $catalogId, array $sections, ?string $footer = null): static
    {
        $interactive = [
            'type'   => 'product_list',
            'body'   => ['text' => $body],
            'action' => [
                'catalog_id' => $catalogId,
                'sections'   => $sections,
            ],
        ];

        if ($footer !== null) {
            $interactive['footer'] = ['text' => $footer];
        }

        return $this->interactive($interactive);
    }

    public function send(): MessageResponseDTO
    {
        return match (true) {
            $this->textBody !== null => $this->service->sendText(
                new SendTextMessageDTO(
                    to:         $this->phone,
                    body:       $this->textBody,
                    previewUrl: $this->previewUrl,
                    contextMessageId: $this->contextMessageId,
                ),
            ),

            $this->templateName !== null => $this->service->sendTemplate(
                new SendTemplateMessageDTO(
                    to:           $this->phone,
                    templateName: $this->templateName,
                    languageCode: $this->templateLanguage,
                    components:   $this->templateComponents,
                    contextMessageId: $this->contextMessageId,
                ),
            ),

            $this->mediaUrl !== null && $this->mediaType !== null => $this->service->sendMedia(
                new SendMediaMessageDTO(
                    to:        $this->phone,
                    mediaType: $this->mediaType,
                    url:       new MediaUrl($this->mediaUrl),
                    caption:   $this->mediaCaption,
                    filename:  $this->mediaFilename,
                    contextMessageId: $this->contextMessageId,
                ),
            ),

            $this->reactionMessageId !== null && $this->reactionEmoji !== null => $this->service->sendReaction(
                new SendReactionMessageDTO(
                    to:        $this->phone,
                    messageId: $this->reactionMessageId,
                    emoji:     $this->reactionEmoji,
                ),
            ),

            $this->latitude !== null && $this->longitude !== null => $this->service->sendLocation(
                new SendLocationMessageDTO(
                    to:               $this->phone,
                    latitude:         $this->latitude,
                    longitude:        $this->longitude,
                    name:             $this->locationName,
                    address:          $this->locationAddress,
                    contextMessageId: $this->contextMessageId,
                ),
            ),

            $this->contacts !== [] => $this->service->sendContacts(
                new SendContactsMessageDTO(
                    to:               $this->phone,
                    contacts:         $this->contacts,
                    contextMessageId: $this->contextMessageId,
                ),
            ),

            $this->interactive !== null => $this->service->sendInteractive(
                new SendInteractiveMessageDTO(
                    to:               $this->phone,
                    interactive:      $this->interactive,
                    contextMessageId: $this->contextMessageId,
                ),
            ),

            default => throw new WBMetaException(
                'No message type selected. Call text(), template(), image(), audio(), video(), document(), sticker(), reaction(), location(), contacts(), or interactive() before send().',
            ),
        };
    }
}
