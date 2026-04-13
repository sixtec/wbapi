<?php

declare(strict_types=1);

namespace Sixtec\WBApi\Tests\Unit\Builders;

use PHPUnit\Framework\TestCase;
use Sixtec\WBApi\Builders\MessageBuilder;
use Sixtec\WBApi\Config\WBMetaConfig;
use Sixtec\WBApi\DTOs\MessageResponseDTO;
use Sixtec\WBApi\Exceptions\WBMetaException;
use Sixtec\WBApi\Services\MessagingService;
use Sixtec\WBApi\Tests\Fakes\FakeHttpClient;

final class MessageBuilderTest extends TestCase
{
    private FakeHttpClient $httpClient;
    private MessagingService $service;

    protected function setUp(): void
    {
        $config           = new WBMetaConfig(accessToken: 'fake', phoneNumberId: '123');
        $this->httpClient = new FakeHttpClient();
        $this->service    = new MessagingService($this->httpClient, $config);
    }

    public function testSendTextReturnsMessageResponseDTO(): void
    {
        $this->httpClient->addResponse(200, [
            'contacts' => [['input' => '5511999999999']],
            'messages' => [['id' => 'wamid.text01', 'message_status' => 'accepted']],
        ]);

        $response = (new MessageBuilder($this->service, '5511999999999'))
            ->text('Hello World!')
            ->send();

        $this->assertInstanceOf(MessageResponseDTO::class, $response);
        $this->assertSame('wamid.text01', $response->messageId->getValue());
        $this->assertSame('accepted', $response->status);
    }

    public function testTextPayloadStructure(): void
    {
        $this->httpClient->addResponse(200, [
            'contacts' => [['input' => '5511999999999']],
            'messages' => [['id' => 'wamid.text02', 'message_status' => 'accepted']],
        ]);

        (new MessageBuilder($this->service, '5511999999999'))
            ->text('Test body', true)
            ->send();

        $req = $this->httpClient->getLastRequest();

        $this->assertSame('POST', $req['method']);
        $this->assertSame('text', $req['payload']['type']);
        $this->assertSame('Test body', $req['payload']['text']['body']);
        $this->assertTrue($req['payload']['text']['preview_url']);
        $this->assertSame('5511999999999', $req['payload']['to']);
        $this->assertSame('whatsapp', $req['payload']['messaging_product']);
    }

    public function testSendImageMessage(): void
    {
        $this->httpClient->addResponse(200, [
            'contacts' => [['input' => '5511999999999']],
            'messages' => [['id' => 'wamid.img01', 'message_status' => 'accepted']],
        ]);

        $response = (new MessageBuilder($this->service, '5511999999999'))
            ->image('https://example.com/img.jpg', 'My caption')
            ->send();

        $this->assertSame('wamid.img01', $response->messageId->getValue());

        $req = $this->httpClient->getLastRequest();
        $this->assertSame('image', $req['payload']['type']);
        $this->assertSame('https://example.com/img.jpg', $req['payload']['image']['link']);
        $this->assertSame('My caption', $req['payload']['image']['caption']);
    }

    public function testSendAudioOmitsCaption(): void
    {
        $this->httpClient->addResponse(200, [
            'contacts' => [['input' => '5511999999999']],
            'messages' => [['id' => 'wamid.audio01', 'message_status' => 'accepted']],
        ]);

        (new MessageBuilder($this->service, '5511999999999'))
            ->audio('https://example.com/audio.ogg')
            ->send();

        $req = $this->httpClient->getLastRequest();
        $this->assertSame('audio', $req['payload']['type']);
        $this->assertArrayNotHasKey('caption', $req['payload']['audio']);
    }

    public function testSendDocumentWithFilename(): void
    {
        $this->httpClient->addResponse(200, [
            'contacts' => [['input' => '5511999999999']],
            'messages' => [['id' => 'wamid.doc01', 'message_status' => 'accepted']],
        ]);

        (new MessageBuilder($this->service, '5511999999999'))
            ->document('https://example.com/file.pdf', 'report.pdf', 'Q1 Report')
            ->send();

        $req = $this->httpClient->getLastRequest();
        $this->assertSame('document', $req['payload']['type']);
        $this->assertSame('report.pdf', $req['payload']['document']['filename']);
        $this->assertSame('Q1 Report', $req['payload']['document']['caption']);
    }

    public function testSendTemplateMessage(): void
    {
        $this->httpClient->addResponse(200, [
            'contacts' => [['input' => '5511999999999']],
            'messages' => [['id' => 'wamid.tpl01', 'message_status' => 'accepted']],
        ]);

        (new MessageBuilder($this->service, '5511999999999'))
            ->template('hello_world', 'en_US')
            ->send();

        $req = $this->httpClient->getLastRequest();
        $this->assertSame('template', $req['payload']['type']);
        $this->assertSame('hello_world', $req['payload']['template']['name']);
        $this->assertSame('en_US', $req['payload']['template']['language']['code']);
    }

    public function testThrowsWithoutMessageType(): void
    {
        $this->expectException(WBMetaException::class);

        (new MessageBuilder($this->service, '5511999999999'))->send();
    }
}
