<?php

declare(strict_types=1);

namespace Sixtec\WBApi\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Sixtec\WBApi\Config\WBMetaConfig;
use Sixtec\WBApi\Services\MessagingService;
use Sixtec\WBApi\Tests\Fakes\FakeHttpClient;
use Sixtec\WBApi\WBMeta;

final class SendMessageTest extends TestCase
{
    private FakeHttpClient $httpClient;

    protected function setUp(): void
    {
        $config           = new WBMetaConfig(accessToken: 'fake', phoneNumberId: '123');
        $this->httpClient = new FakeHttpClient();

        WBMeta::configure($config);
        WBMeta::bindService(new MessagingService($this->httpClient, $config));
    }

    protected function tearDown(): void
    {
        WBMeta::reset();
    }

    public function testFacadeSendsTextMessage(): void
    {
        $this->httpClient->addResponse(200, [
            'contacts' => [['input' => '5511999999999']],
            'messages' => [['id' => 'wamid.f001', 'message_status' => 'accepted']],
        ]);

        $response = WBMeta::to('5511999999999')
            ->text('Hello from WBMeta!')
            ->send();

        $this->assertSame('wamid.f001', $response->messageId->getValue());
        $this->assertSame('accepted', $response->status);

        $req = $this->httpClient->getLastRequest();
        $this->assertSame('Hello from WBMeta!', $req['payload']['text']['body']);
    }

    public function testFacadeSendsImageMessage(): void
    {
        $this->httpClient->addResponse(200, [
            'contacts' => [['input' => '5511999999999']],
            'messages' => [['id' => 'wamid.f002', 'message_status' => 'accepted']],
        ]);

        $response = WBMeta::to('5511999999999')
            ->image('https://example.com/photo.jpg', 'Check this out!')
            ->send();

        $this->assertSame('wamid.f002', $response->messageId->getValue());
        $this->assertSame('image', $this->httpClient->getLastRequest()['payload']['type']);
        $this->assertSame('Check this out!', $this->httpClient->getLastRequest()['payload']['image']['caption']);
    }

    public function testFacadeSendsTemplateMessage(): void
    {
        $this->httpClient->addResponse(200, [
            'contacts' => [['input' => '5511999999999']],
            'messages' => [['id' => 'wamid.f003', 'message_status' => 'accepted']],
        ]);

        $response = WBMeta::to('5511999999999')
            ->template('hello_world', 'en_US')
            ->send();

        $this->assertSame('wamid.f003', $response->messageId->getValue());

        $req = $this->httpClient->getLastRequest();
        $this->assertSame('template', $req['payload']['type']);
        $this->assertSame('hello_world', $req['payload']['template']['name']);
        $this->assertSame('en_US', $req['payload']['template']['language']['code']);
    }

    public function testFacadeSendsVideoMessage(): void
    {
        $this->httpClient->addResponse(200, [
            'contacts' => [['input' => '5511999999999']],
            'messages' => [['id' => 'wamid.f004', 'message_status' => 'accepted']],
        ]);

        WBMeta::to('5511999999999')
            ->video('https://example.com/video.mp4', 'Watch this')
            ->send();

        $req = $this->httpClient->getLastRequest();
        $this->assertSame('video', $req['payload']['type']);
        $this->assertSame('Watch this', $req['payload']['video']['caption']);
    }

    public function testFacadeThrowsWhenNotConfigured(): void
    {
        WBMeta::reset();

        $this->expectException(\RuntimeException::class);

        WBMeta::to('5511999999999')->text('Hi')->send();
    }
}
