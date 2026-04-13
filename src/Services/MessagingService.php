<?php

declare(strict_types=1);

namespace Sixtec\WBApi\Services;

use Sixtec\WBApi\Config\WBMetaConfig;
use Sixtec\WBApi\Contracts\HttpClientInterface;
use Sixtec\WBApi\Domain\ValueObjects\MessageId;
use Sixtec\WBApi\DTOs\MessageResponseDTO;
use Sixtec\WBApi\DTOs\SendMediaMessageDTO;
use Sixtec\WBApi\DTOs\SendTemplateMessageDTO;
use Sixtec\WBApi\DTOs\SendTextMessageDTO;
use Sixtec\WBApi\Exceptions\HttpException;
use Sixtec\WBApi\Mappers\MediaMessageMapper;
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

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly WBMetaConfig $config,
    ) {
        $this->textMapper     = new TextMessageMapper();
        $this->templateMapper = new TemplateMessageMapper();
        $this->mediaMapper    = new MediaMessageMapper();
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
