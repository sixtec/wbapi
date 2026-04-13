# sixtec/wbapi

Biblioteca PHP 8.1+ para integração com a **Meta Cloud API (WhatsApp Business)**.

Construída com Clean Architecture, Fluent Interface, DTOs e client HTTP desacoplado — sem expor nenhum payload bruto da Meta ao consumidor da biblioteca.

> **Autor:** Mário Lucas  
> **Criação:** 12 de abril de 2026  
> **Licença:** MIT  

---

## Requisitos

| Requisito | Versão |
|-----------|--------|
| PHP       | ^8.1   |
| Guzzle    | ^7.0   |

---

## Instalação

```bash
composer require sixtec/wbapi
```

---

## Configuração

```php
use Sixtec\WBApi\WBMeta;
use Sixtec\WBApi\Config\WBMetaConfig;

WBMeta::configure(new WBMetaConfig(
    accessToken:        'EAAxxxxxxxxxxxxxxx',
    phoneNumberId:      '123456789012345',
    apiVersion:         'v19.0',           // opcional, padrão: v19.0
    webhookVerifyToken: 'meu-token-secreto', // necessário para webhooks
    retryAttempts:      3,                 // opcional, padrão: 3
    timeout:            30.0,              // opcional, padrão: 30s
));
```

---

## Envio de Mensagens

### Texto simples

```php
WBMeta::to('+5511999999999')
    ->text('Olá, tudo bem?')
    ->send();
```

### Texto com preview de URL

```php
WBMeta::to('+5511999999999')
    ->text('Acesse: https://example.com', previewUrl: true)
    ->send();
```

### Imagem

```php
WBMeta::to('+5511999999999')
    ->image('https://example.com/foto.jpg', 'Legenda opcional')
    ->send();
```

### Vídeo

```php
WBMeta::to('+5511999999999')
    ->video('https://example.com/video.mp4', 'Legenda opcional')
    ->send();
```

### Áudio

```php
WBMeta::to('+5511999999999')
    ->audio('https://example.com/audio.ogg')
    ->send();
```

### Documento

```php
WBMeta::to('+5511999999999')
    ->document('https://example.com/relatorio.pdf', 'relatorio.pdf', 'Relatório Q1')
    ->send();
```

### Template

```php
use Sixtec\WBApi\DTOs\TemplateComponentDTO;
use Sixtec\WBApi\DTOs\TemplateParameterDTO;

WBMeta::to('+5511999999999')
    ->template('hello_world', 'pt_BR', [
        new TemplateComponentDTO(
            type: 'body',
            parameters: [
                new TemplateParameterDTO(type: 'text', value: 'João'),
            ],
        ),
    ])
    ->send();
```

### Retorno

Todos os métodos `send()` retornam um `MessageResponseDTO`:

```php
use Sixtec\WBApi\DTOs\MessageResponseDTO;

$response = WBMeta::to('+5511999999999')->text('Olá!')->send();

echo $response->messageId->getValue(); // wamid.HBgLNTUxMTk...
echo $response->status;               // accepted
echo $response->to;                   // 5511999999999
```

---

## Webhooks

### 1. Verificação do Desafio (GET)

```php
$handler   = WBMeta::webhook();
$challenge = $handler->verify(
    $_GET['hub_mode'],
    $_GET['hub_verify_token'],
    $_GET['hub_challenge'],
);

http_response_code(200);
echo $challenge;
```

### 2. Recebimento de Eventos (POST)

```php
use Sixtec\WBApi\Webhook\Events\MessageReceivedEvent;
use Sixtec\WBApi\Webhook\Events\MessageDeliveredEvent;
use Sixtec\WBApi\Webhook\Events\MessageReadEvent;

$payload = json_decode(file_get_contents('php://input'), true);
$events  = WBMeta::webhook()->handle($payload);

foreach ($events as $event) {
    match (true) {
        $event instanceof MessageReceivedEvent  => handleReceived($event),
        $event instanceof MessageDeliveredEvent => handleDelivered($event),
        $event instanceof MessageReadEvent      => handleRead($event),
    };
}

function handleReceived(MessageReceivedEvent $event): void
{
    // $event->messageId, $event->from, $event->type
    // $event->textBody  — preenchido quando type === 'text'
    // $event->mediaData — preenchido para tipos de mídia
}
```

---

## Arquitetura

```
src/
├── WBMeta.php                          # Facade estática (ponto de entrada)
├── Config/
│   └── WBMetaConfig.php                # DTO de configuração
├── Contracts/
│   ├── HttpClientInterface.php         # Contrato do client HTTP
│   └── TokenStorageInterface.php       # Contrato de armazenamento de token
├── Domain/
│   ├── Entities/                       # Message, Contact, Conversation, Template
│   └── ValueObjects/                   # PhoneNumber, MessageId, MediaUrl
├── DTOs/                               # Objetos de transferência (entrada/saída)
│   ├── SendTextMessageDTO.php
│   ├── SendTemplateMessageDTO.php
│   ├── SendMediaMessageDTO.php
│   ├── MessageResponseDTO.php
│   ├── TemplateComponentDTO.php
│   ├── TemplateParameterDTO.php
│   └── MediaType.php  (enum)
├── Http/
│   ├── GuzzleHttpClient.php            # Adapter Guzzle + retry automático
│   └── HttpResponse.php
├── Auth/
│   ├── TokenManager.php
│   └── InMemoryTokenStorage.php
├── Mappers/                            # Convertem DTOs → payload Meta
│   ├── TextMessageMapper.php
│   ├── TemplateMessageMapper.php
│   └── MediaMessageMapper.php
├── Services/
│   └── MessagingService.php            # Orquestra envio, desacoplado via interface
├── Builders/
│   └── MessageBuilder.php             # Fluent interface
├── Webhook/
│   ├── WebhookHandler.php             # verify() + handle()
│   ├── WebhookPayloadParser.php
│   └── Events/
│       ├── MessageReceivedEvent.php
│       ├── MessageDeliveredEvent.php
│       └── MessageReadEvent.php
└── Exceptions/
    ├── WBMetaException.php
    ├── HttpException.php
    └── WebhookVerificationException.php
```

### Princípios aplicados

| Princípio | Aplicação |
|-----------|-----------|
| **Clean Architecture** | Domain isolado de infraestrutura (HTTP, Auth) |
| **Fluent Interface** | `WBMeta::to()->text()->send()` |
| **DTOs** | Nunca expostos payloads brutos da Meta |
| **Mappers** | Conversão DTO → payload em classes dedicadas |
| **Dependency Inversion** | `HttpClientInterface` e `TokenStorageInterface` |
| **PSR-4** | Autoload `Sixtec\WBApi\` → `src/` |

---

## Injeção de Dependências / Frameworks

Para integrar em um container DI (Laravel, Symfony, etc.), injete diretamente o `MessagingService`:

```php
use Sixtec\WBApi\Config\WBMetaConfig;
use Sixtec\WBApi\Http\GuzzleHttpClient;
use Sixtec\WBApi\Services\MessagingService;

$config  = new WBMetaConfig(accessToken: env('WA_TOKEN'), phoneNumberId: env('WA_PHONE_ID'));
$service = new MessagingService(new GuzzleHttpClient($config), $config);

// Registrar no container e injetar onde necessário
```

---

## Testes

```bash
# Todos os testes
./vendor/bin/phpunit

# Apenas unitários
./vendor/bin/phpunit --testsuite Unit

# Apenas integração
./vendor/bin/phpunit --testsuite Integration
```

A suite cobre **26 testes / 73 assertions** usando `FakeHttpClient` — sem chamadas reais à API.

Para testar com sua própria lógica, injete um `MessagingService` com `FakeHttpClient` via `WBMeta::bindService()`:

```php
use Sixtec\WBApi\Tests\Fakes\FakeHttpClient;
use Sixtec\WBApi\Services\MessagingService;

$fake = new FakeHttpClient();
$fake->addResponse(200, [
    'contacts' => [['input' => '5511999999999']],
    'messages' => [['id' => 'wamid.test01', 'message_status' => 'accepted']],
]);

WBMeta::bindService(new MessagingService($fake, $config));
```

---

## Exceções

| Classe | Quando é lançada |
|--------|-----------------|
| `WBMetaException` | Base — erros gerais da biblioteca |
| `HttpException` | Resposta HTTP não-2xx ou falha de rede |
| `WebhookVerificationException` | Token ou mode inválido no desafio do webhook |

---

## Licença

MIT © Mário Lucas
