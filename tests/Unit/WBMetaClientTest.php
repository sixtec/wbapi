<?php

declare(strict_types=1);

namespace Sixtec\WBApi\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Sixtec\WBApi\Config\WBMetaConfig;
use Sixtec\WBApi\Tests\Fakes\FakeHttpClient;
use Sixtec\WBApi\WBMeta;
use Sixtec\WBApi\WBMetaClient;

final class WBMetaClientTest extends TestCase
{
    protected function tearDown(): void
    {
        WBMeta::reset();
    }

    public function testClientSendsMessagesWithInjectedHttpClient(): void
    {
        $httpClient = new FakeHttpClient();
        $httpClient->addResponse(200, [
            'contacts' => [['input' => '5511999999999']],
            'messages' => [['id' => 'wamid.client01', 'message_status' => 'accepted']],
        ]);

        $client = WBMetaClient::fromConfig(
            new WBMetaConfig(accessToken: 'fake', phoneNumberId: '123'),
            $httpClient,
        );

        $response = $client->to('5511999999999')->text('Hello from client')->send();

        $this->assertSame('wamid.client01', $response->messageId->getValue());
        $this->assertSame('Hello from client', $httpClient->getLastRequest()['payload']['text']['body']);
    }

    public function testClientInstancesKeepConfigurationsIsolated(): void
    {
        $firstHttp = new FakeHttpClient();
        $secondHttp = new FakeHttpClient();

        $firstHttp->addResponse(200, [
            'contacts' => [['input' => '5511999999999']],
            'messages' => [['id' => 'wamid.first', 'message_status' => 'accepted']],
        ]);
        $secondHttp->addResponse(200, [
            'contacts' => [['input' => '5581999999999']],
            'messages' => [['id' => 'wamid.second', 'message_status' => 'accepted']],
        ]);

        $first = WBMetaClient::fromConfig(new WBMetaConfig(accessToken: 'fake', phoneNumberId: '111'), $firstHttp);
        $second = WBMetaClient::fromConfig(new WBMetaConfig(accessToken: 'fake', phoneNumberId: '222'), $secondHttp);

        $first->to('5511999999999')->text('First')->send();
        $second->to('5581999999999')->text('Second')->send();

        $this->assertStringEndsWith('/111/messages', $firstHttp->getLastRequest()['url']);
        $this->assertStringEndsWith('/222/messages', $secondHttp->getLastRequest()['url']);
    }

    public function testFacadeExposesConfiguredClient(): void
    {
        WBMeta::configure(new WBMetaConfig(accessToken: 'fake', phoneNumberId: '123'));

        $this->assertInstanceOf(WBMetaClient::class, WBMeta::client());
        $this->assertSame('123', WBMeta::client()->getConfig()->phoneNumberId);
    }
}
