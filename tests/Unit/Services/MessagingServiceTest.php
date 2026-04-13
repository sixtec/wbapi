<?php

declare(strict_types=1);

namespace Sixtec\WBApi\Tests\Unit\Services;

use PHPUnit\Framework\TestCase;
use Sixtec\WBApi\Config\WBMetaConfig;
use Sixtec\WBApi\Domain\ValueObjects\MediaUrl;
use Sixtec\WBApi\Domain\ValueObjects\PhoneNumber;
use Sixtec\WBApi\DTOs\MediaType;
use Sixtec\WBApi\DTOs\MessageResponseDTO;
use Sixtec\WBApi\DTOs\SendMediaMessageDTO;
use Sixtec\WBApi\DTOs\SendTemplateMessageDTO;
use Sixtec\WBApi\DTOs\SendTextMessageDTO;
use Sixtec\WBApi\Exceptions\HttpException;
use Sixtec\WBApi\Services\MessagingService;
use Sixtec\WBApi\Tests\Fakes\FakeHttpClient;

final class MessagingServiceTest extends TestCase
{
    private FakeHttpClient $httpClient;
    private MessagingService $service;

    protected function setUp(): void
    {
        $config           = new WBMetaConfig(accessToken: 'fake', phoneNumberId: '123');
        $this->httpClient = new FakeHttpClient();
        $this->service    = new MessagingService($this->httpClient, $config);
    }

    public function testSendTextReturnsDTO(): void
    {
        $this->httpClient->addResponse(200, [
            'contacts' => [['input' => '5511999999999']],
            'messages' => [['id' => 'wamid.abc', 'message_status' => 'accepted']],
        ]);

        $response = $this->service->sendText(new SendTextMessageDTO(
            to: new PhoneNumber('5511999999999'),
            body: 'Hello!',
        ));

        $this->assertInstanceOf(MessageResponseDTO::class, $response);
        $this->assertSame('wamid.abc', $response->messageId->getValue());
        $this->assertSame('accepted', $response->status);
        $this->assertSame('5511999999999', $response->to);
    }

    public function testSendTextThrowsOnNon2xxResponse(): void
    {
        $this->httpClient->addResponse(400, [
            'error' => ['message' => 'Invalid phone number', 'code' => 131026],
        ]);

        $this->expectException(HttpException::class);
        $this->expectExceptionMessageMatches('/\[400\]/');

        $this->service->sendText(new SendTextMessageDTO(
            to: new PhoneNumber('5511999999999'),
            body: 'Hello!',
        ));
    }

    public function testSendTemplateBuildsCorrectPayload(): void
    {
        $this->httpClient->addResponse(200, [
            'contacts' => [['input' => '5511999999999']],
            'messages' => [['id' => 'wamid.tpl', 'message_status' => 'accepted']],
        ]);

        $this->service->sendTemplate(new SendTemplateMessageDTO(
            to:           new PhoneNumber('5511999999999'),
            templateName: 'hello_world',
            languageCode: 'en_US',
        ));

        $req = $this->httpClient->getLastRequest();
        $this->assertSame('template', $req['payload']['type']);
        $this->assertSame('hello_world', $req['payload']['template']['name']);
    }

    public function testSendMediaReturnsDTO(): void
    {
        $this->httpClient->addResponse(200, [
            'contacts' => [['input' => '5511999999999']],
            'messages' => [['id' => 'wamid.media', 'message_status' => 'accepted']],
        ]);

        $response = $this->service->sendMedia(new SendMediaMessageDTO(
            to:        new PhoneNumber('5511999999999'),
            mediaType: MediaType::Image,
            url:       new MediaUrl('https://example.com/img.png'),
            caption:   'Test',
        ));

        $this->assertSame('wamid.media', $response->messageId->getValue());

        $req = $this->httpClient->getLastRequest();
        $this->assertSame('image', $req['payload']['type']);
        $this->assertSame('Test', $req['payload']['image']['caption']);
    }

    public function testThrowsWhenResponseMissingMessageId(): void
    {
        $this->httpClient->addResponse(200, [
            'contacts' => [['input' => '5511999999999']],
            'messages' => [],
        ]);

        $this->expectException(HttpException::class);

        $this->service->sendText(new SendTextMessageDTO(
            to: new PhoneNumber('5511999999999'),
            body: 'Hi',
        ));
    }
}
