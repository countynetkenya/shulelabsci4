# Integrations Module

The Integrations module provides a unified, first-class interface for managing all third-party integrations in the ShuleLabs CI4 system. It centralizes integration logic, logging, retry mechanisms, webhook handling, and mobile/offline support.

## Architecture

### Core Components

1. **IntegrationService** - Central service for executing integration operations with logging, idempotency, and retry support
2. **Adapters** - Implementation classes for specific integrations (M-Pesa, WhatsApp, Moodle, etc.)
3. **Interfaces** - Contracts that adapters must implement (PaymentGatewayInterface, SMSGatewayInterface, etc.)
4. **Configuration** - Integration settings, adapter mappings, and retry policies
5. **Database Layer** - Tables for integrations, logs, webhooks, auth tokens, and offline queue

### Design Principles

- **Separation of Concerns**: No module talks directly to external services - everything flows through the Integrations module
- **Idempotency**: All operations use idempotency keys to prevent duplicate execution
- **Audit Trail**: Complete logging of all requests, responses, and errors
- **Retry Logic**: Automatic retry with configurable backoff strategies for failed operations
- **Mobile-First**: Support for offline queuing and progressive enhancement
- **Type Safety**: Strong typing with interfaces and documented return types

## Directory Structure

```
Integrations/
├── Config/
│   ├── Integrations.php    # Configuration for adapters and settings
│   └── Routes.php           # API routes for webhooks and management
├── Services/
│   ├── IntegrationService.php  # Core service
│   ├── Interfaces/             # Contracts for adapters
│   │   ├── IntegrationAdapterInterface.php
│   │   ├── PaymentGatewayInterface.php
│   │   ├── SMSGatewayInterface.php
│   │   ├── StorageInterface.php
│   │   └── LmsInterface.php
│   ├── Adapters/               # Implementation of integrations
│   │   ├── BaseAdapter.php
│   │   ├── Payment/            # M-Pesa, Flutterwave, Pesapal
│   │   ├── Communication/      # SMS, WhatsApp
│   │   ├── Storage/            # Google Drive, Local Storage
│   │   └── LMS/                # Moodle
│   └── Handlers/               # Webhook, Retry, Error handlers
├── Controllers/
│   ├── IntegrationController.php
│   ├── WebhookController.php
│   ├── OAuthController.php
│   └── LogController.php
├── Models/
│   ├── IntegrationModel.php
│   ├── IntegrationLogModel.php
│   └── OfflineQueueModel.php
├── Database/
│   └── Migrations/             # Database schema
├── Jobs/                       # Background jobs
├── Events/                     # Integration events
├── Commands/                   # CLI commands
└── Tests/
    ├── Unit/                   # Unit tests for adapters
    └── Feature/                # Integration tests
```

## Usage

### 1. Accessing the Integration Service

```php
// Via service locator
$integrations = service('integrations');

// Or via dependency injection
public function __construct(IntegrationService $integrations)
{
    $this->integrations = $integrations;
}
```

### 2. Executing an Integration

```php
// Send SMS
$response = service('integrations')->execute(
    'sms',
    'send',
    [
        'to' => '+254712345678',
        'message' => 'Your payment has been received',
        'from' => 'SHULELABS'
    ],
    ['tenant_id' => 'school123', 'user_id' => 1]
);

// Process M-Pesa payment
$response = service('integrations')->execute(
    'mpesa',
    'charge',
    [
        'amount' => 1000.0,
        'currency' => 'KES',
        'phone' => '254712345678',
        'reference' => 'FEE-2024-001'
    ],
    ['tenant_id' => 'school123', 'user_id' => 1]
);

// Upload file to Google Drive
$response = service('integrations')->execute(
    'google_drive',
    'upload',
    [
        'file_path' => '/path/to/file.pdf',
        'destination' => 'reports/2024/'
    ],
    ['tenant_id' => 'school123']
);
```

### 3. Checking Integration Health

```php
$health = service('integrations')->checkHealth('mpesa');
// Returns: ['status' => 'ok', 'message' => 'M-Pesa adapter is operational']
```

## Supported Integrations

### Payment Gateways (PaymentGatewayInterface)
- **M-Pesa** - Mobile money payments (Kenya)
- **Flutterwave** - Multi-channel payment gateway
- **Pesapal** - East African payment processor

Operations: `charge`, `query`, `refund`

### Communication (SMSGatewayInterface)
- **SMS (Africa's Talking)** - SMS messaging
- **WhatsApp** - WhatsApp Business API

Operations: `send`, `query`, `balance`

### Storage (StorageInterface)
- **Google Drive** - Cloud file storage
- **Local Storage** - Local file system (for offline/development)

Operations: `upload`, `download`, `delete`, `list`

### Learning Management (LmsInterface)
- **Moodle** - LMS integration for courses and grades

Operations: `enroll`, `sync_grades`, `sync_courses`, `create_course`

## Configuration

### Environment Variables

Add to your `.env` file:

```env
# M-Pesa Configuration
MPESA_CONSUMER_KEY=your_consumer_key
MPESA_CONSUMER_SECRET=your_consumer_secret
MPESA_SHORTCODE=your_shortcode
MPESA_PASSKEY=your_passkey
MPESA_ENVIRONMENT=sandbox

# Flutterwave Configuration
FLUTTERWAVE_PUBLIC_KEY=your_public_key
FLUTTERWAVE_SECRET_KEY=your_secret_key
FLUTTERWAVE_ENCRYPTION_KEY=your_encryption_key

# SMS Configuration (Africa's Talking)
SMS_USERNAME=your_username
SMS_API_KEY=your_api_key
SMS_SENDER_ID=SHULELABS

# WhatsApp Configuration
WHATSAPP_PHONE_NUMBER_ID=your_phone_number_id
WHATSAPP_ACCESS_TOKEN=your_access_token

# Google Drive Configuration
GOOGLE_DRIVE_CLIENT_ID=your_client_id
GOOGLE_DRIVE_CLIENT_SECRET=your_client_secret
GOOGLE_DRIVE_REFRESH_TOKEN=your_refresh_token

# Moodle Configuration
MOODLE_BASE_URL=https://your-moodle-instance.com
MOODLE_TOKEN=your_api_token
```

### Enabling Integrations

Edit `app/Modules/Integrations/Config/Integrations.php`:

```php
public array $adapters = [
    'mpesa' => [
        'adapter' => MpesaAdapter::class,
        'enabled' => true,  // Set to true to enable
    ],
    // ... other adapters
];
```

## Adding a New Integration

### 1. Create an Interface (if needed)

```php
namespace Modules\Integrations\Services\Interfaces;

interface MyServiceInterface extends IntegrationAdapterInterface
{
    public function myOperation(array $payload, array $context): array;
}
```

### 2. Create an Adapter

```php
namespace Modules\Integrations\Services\Adapters\MyCategory;

use Modules\Integrations\Services\Adapters\BaseAdapter;

class MyServiceAdapter extends BaseAdapter implements MyServiceInterface
{
    public function getName(): string
    {
        return 'my_service';
    }

    public function execute(string $operation, array $payload, array $context): array
    {
        return match ($operation) {
            'my_operation' => $this->myOperation($payload, $context),
            default => throw new RuntimeException("Unknown operation: {$operation}"),
        };
    }

    public function myOperation(array $payload, array $context): array
    {
        // Implement your integration logic
        return ['status' => 'success'];
    }

    public function checkStatus(): array
    {
        return ['status' => 'ok'];
    }

    protected function getRequiredConfigKeys(): array
    {
        return ['api_key', 'api_secret'];
    }
}
```

### 3. Register in Configuration

Add to `Config/Integrations.php`:

```php
public array $adapters = [
    'my_service' => [
        'adapter' => MyServiceAdapter::class,
        'enabled' => true,
    ],
];
```

### 4. Use the Integration

```php
$response = service('integrations')->execute(
    'my_service',
    'my_operation',
    ['data' => 'value'],
    ['tenant_id' => 'school123']
);
```

## Database Schema

The module creates the following tables:

1. **ci4_integration_integrations** - Integration configurations
2. **ci4_integration_logs** - Detailed operation logs
3. **ci4_integration_auth_tokens** - OAuth and API tokens
4. **ci4_integration_webhooks** - Webhook configurations
5. **ci4_integration_webhook_logs** - Webhook event logs
6. **ci4_integration_offline_queue** - Offline sync queue

## API Endpoints

All endpoints are under `/api/v2/integrations`:

- `GET /health` - Overall health check
- `GET /health/:adapter` - Check specific adapter
- `GET /` - List all integrations
- `GET /:adapter/status` - Get adapter status
- `POST /:adapter/webhook` - Receive webhooks
- `GET /:adapter/oauth/callback` - OAuth callbacks
- `GET /logs` - View integration logs
- `GET /logs/:id` - View specific log entry

## Mobile & Offline Support

The module includes support for mobile apps with intermittent connectivity:

1. **Offline Queue** - Queue operations when offline
2. **Auto-sync** - Automatically sync when connection is restored
3. **Progressive Enhancement** - Fallback notification channels (push → WhatsApp → SMS)
4. **Response Compression** - Smaller payloads for mobile

## Testing

Run tests with:

```bash
vendor/bin/phpunit --filter Integrations
```

## Security Considerations

- All sensitive configuration (API keys, secrets) should be stored in environment variables
- Webhook signatures are verified when enabled
- Auth tokens are stored securely in the database
- All operations are logged for audit purposes
- Idempotency keys prevent duplicate operations

## Future Enhancements

- Admin UI for managing integrations
- Real-time monitoring dashboard
- Advanced retry strategies
- Integration marketplace
- More adapters (QuickBooks, KNEC, Maps, etc.)
