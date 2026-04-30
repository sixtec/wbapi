<?php

declare(strict_types=1);

namespace Sixtec\WBApi\Services;

use Sixtec\WBApi\Config\WBMetaConfig;
use Sixtec\WBApi\Contracts\HttpClientInterface;
use Sixtec\WBApi\Domain\ValueObjects\MessageId;
use Sixtec\WBApi\DTOs\MarkMessageAsReadDTO;
use Sixtec\WBApi\DTOs\MessageResponseDTO;
use Sixtec\WBApi\DTOs\SendContactsMessageDTO;
use Sixtec\WBApi\DTOs\SendInteractiveMessageDTO;
use Sixtec\WBApi\DTOs\SendLocationMessageDTO;
use Sixtec\WBApi\DTOs\SendMediaMessageDTO;
use Sixtec\WBApi\DTOs\SendReactionMessageDTO;
use Sixtec\WBApi\DTOs\SendTemplateMessageDTO;
use Sixtec\WBApi\DTOs\SendTextMessageDTO;
use Sixtec\WBApi\Exceptions\HttpException;
use Sixtec\WBApi\Mappers\ContactsMessageMapper;
use Sixtec\WBApi\Mappers\InteractiveMessageMapper;
use Sixtec\WBApi\Mappers\LocationMessageMapper;
use Sixtec\WBApi\Mappers\MediaMessageMapper;
use Sixtec\WBApi\Mappers\ReactionMessageMapper;
use Sixtec\WBApi\Mappers\TemplateMessageMapper;
use Sixtec\WBApi\Mappers\TextMessageMapper;

/**
 * @author Mário Lucas
 * @since  2026-04-12
 */
final class MessagingService
{
    private readonly TextMessageMapper $textMapper;
    private readonly TemplateMessageMapper $templateMapper;
    private readonly MediaMessageMapper $mediaMapper;
    private readonly ReactionMessageMapper $reactionMapper;
    private readonly LocationMessageMapper $locationMapper;
    private readonly ContactsMessageMapper $contactsMapper;
    private readonly InteractiveMessageMapper $interactiveMapper;

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly WBMetaConfig $config,
    ) {
        $this->textMapper     = new TextMessageMapper();
        $this->templateMapper = new TemplateMessageMapper();
        $this->mediaMapper    = new MediaMessageMapper();
        $this->reactionMapper = new ReactionMessageMapper();
        $this->locationMapper = new LocationMessageMapper();
        $this->contactsMapper = new ContactsMessageMapper();
        $this->interactiveMapper = new InteractiveMessageMapper();
    }

    public function sendText(SendTextMessageDTO $dto): MessageResponseDTO
    {
        return $this->dispatch($this->textMapper->toPayload($dto));
    }

    public function sendTemplate(SendTemplateMessageDTO $dto): MessageResponseDTO
    {
        return $this->dispatch($this->templateMapper->toPayload($dto));
    }

    public function sendMedia(SendMediaMessageDTO $dto): MessageResponseDTO
    {
        return $this->dispatch($this->mediaMapper->toPayload($dto));
    }

    public function sendReaction(SendReactionMessageDTO $dto): MessageResponseDTO
    {
        return $this->dispatch($this->reactionMapper->toPayload($dto));
    }

    public function sendLocation(SendLocationMessageDTO $dto): MessageResponseDTO
    {
        return $this->dispatch($this->locationMapper->toPayload($dto));
    }

    public function sendContacts(SendContactsMessageDTO $dto): MessageResponseDTO
    {
        return $this->dispatch($this->contactsMapper->toPayload($dto));
    }

    public function sendInteractive(SendInteractiveMessageDTO $dto): MessageResponseDTO
    {
        return $this->dispatch($this->interactiveMapper->toPayload($dto));
    }

    public function markAsRead(MarkMessageAsReadDTO $dto): bool
    {
        $response = $this->httpClient->post($this->config->getMessagesUrl(), [
            'messaging_product' => 'whatsapp',
            'status'            => 'read',
            'message_id'        => $dto->messageId,
        ]);

        if (!$response->isSuccessful()) {
            throw HttpException::unexpectedResponse(
                $response->getStatusCode(),
                (string) json_encode($response->getBody()),
            );
        }

        return (bool) $response->get('success', true);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function dispatch(array $payload): MessageResponseDTO
    {
        $response = $this->httpClient->post($this->config->getMessagesUrl(), $payload);

        if (!$response->isSuccessful()) {
            throw HttpException::unexpectedResponse(
                $response->getStatusCode(),
                (string) json_encode($response->getBody()),
            );
        }

        $messages = $response->get('messages', []);
        $contacts = $response->get('contacts', []);

        $rawId = $messages[0]['id']
            ?? throw new HttpException('Missing message ID in API response.');

        return new MessageResponseDTO(
            messageId: new MessageId($rawId),
            to:        $contacts[0]['input'] ?? '',
            status:    $messages[0]['message_status'] ?? 'accepted',
        );
    }
}
