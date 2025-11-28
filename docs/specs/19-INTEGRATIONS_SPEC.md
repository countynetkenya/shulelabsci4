# 🔌 Integrations Module Specification

**Version**: 1.0.0
**Status**: Implemented (Documentation)
**Last Updated**: 2025-11-27

---

## Part 1: Feature Definition (The "What" & "Why")
*Target Audience: Product Owners, Stakeholders, Developers*

### 1.1 Overview
The Integrations module is the "Bridge to the Outside World" for ShuleLabs. It provides a unified adapter-based architecture for connecting to external services including payment gateways (M-Pesa, Flutterwave, Pesapal), communication channels (SMS via Africa's Talking, WhatsApp, Email, Push/FCM), storage services (Google Drive, Local), and learning management systems (Moodle). It handles OAuth flows, webhook processing, and offline queue synchronization.

### 1.2 User Stories

- **As a Parent**, I want to pay school fees via M-Pesa STK Push, so that payment is convenient and instant.
- **As an Admin**, I want to send bulk SMS to parents, so that urgent information reaches everyone quickly.
- **As a Developer**, I want a unified interface for all payment gateways, so that switching providers is seamless.
- **As a System**, I want to queue failed API calls for retry, so that transient failures don't lose data.
- **As an Admin**, I want to configure integration credentials per school, so that each tenant has their own accounts.
- **As a User**, I want to receive push notifications on my phone, so that I'm alerted immediately.

### 1.3 User Workflows

1. **M-Pesa Payment**:
   - Parent initiates fee payment.
   - System triggers STK Push via M-Pesa API.
   - Parent confirms on phone.
   - M-Pesa sends callback to webhook endpoint.
   - System verifies and records payment.
   - Receipt generated and sent.

2. **SMS Notification**:
   - System triggers notification (absence, fee reminder).
   - IntegrationService routes to SMS adapter.
   - Adapter calls Africa's Talking API.
   - Delivery status returned.
   - Status logged and tracked.

3. **Webhook Processing**:
   - External service sends webhook.
   - System validates signature/token.
   - Payload parsed and processed.
   - Action taken (payment recorded, status updated).
   - Acknowledgment returned.

4. **Offline Queue Sync**:
   - API call fails due to network/timeout.
   - Request queued for retry.
   - Background job processes queue.
   - Retry with exponential backoff.
   - Success/failure logged.

### 1.4 Acceptance Criteria

- [ ] Unified IntegrationService for all external calls.
- [ ] Adapter pattern for swappable providers.
- [ ] M-Pesa STK Push and C2B payments working.
- [ ] SMS delivery via Africa's Talking.
- [ ] Email delivery via SMTP or SendGrid.
- [ ] Push notifications via FCM.
- [ ] WhatsApp messages via API.
- [ ] Webhook endpoints with signature verification.
- [ ] Offline queue with retry logic.
- [ ] Credentials configurable per school.
- [ ] All transactions logged for audit.

---

## Part 2: Technical Specification (The "How")
*Target Audience: Developers, Architects*

### 2.1 Database Schema

#### `integration_providers`
Available integration providers.
```sql
CREATE TABLE integration_providers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category ENUM('payment', 'sms', 'email', 'push', 'whatsapp', 'storage', 'lms') NOT NULL,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(50) NOT NULL,
    adapter_class VARCHAR(255) NOT NULL,
    config_schema JSON,
    is_global BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_code (code),
    INDEX idx_category (category)
);
```

#### `integration_configs`
School-specific configuration.
```sql
CREATE TABLE integration_configs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    provider_id INT NOT NULL,
    config_encrypted TEXT NOT NULL,
    environment ENUM('sandbox', 'production') DEFAULT 'sandbox',
    is_enabled BOOLEAN DEFAULT TRUE,
    is_primary BOOLEAN DEFAULT FALSE,
    last_tested_at DATETIME,
    last_test_status VARCHAR(50),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (provider_id) REFERENCES integration_providers(id) ON DELETE CASCADE,
    UNIQUE KEY uk_school_provider (school_id, provider_id)
);
```

#### `integration_logs`
All integration transactions.
```sql
CREATE TABLE integration_logs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    provider_id INT NOT NULL,
    direction ENUM('outbound', 'inbound') NOT NULL,
    operation VARCHAR(100) NOT NULL,
    reference_type VARCHAR(100),
    reference_id INT,
    request_data JSON,
    response_data JSON,
    status ENUM('pending', 'success', 'failed', 'timeout') DEFAULT 'pending',
    status_code INT,
    error_message TEXT,
    duration_ms INT,
    external_reference VARCHAR(255),
    ip_address VARCHAR(45),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (provider_id) REFERENCES integration_providers(id) ON DELETE CASCADE,
    INDEX idx_school_operation (school_id, operation),
    INDEX idx_external_ref (external_reference),
    INDEX idx_created (created_at)
);
```

#### `integration_webhooks`
Webhook endpoint registrations.
```sql
CREATE TABLE integration_webhooks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT,
    provider_id INT NOT NULL,
    endpoint_path VARCHAR(255) NOT NULL,
    secret_key VARCHAR(255),
    events JSON,
    is_active BOOLEAN DEFAULT TRUE,
    last_received_at DATETIME,
    receive_count INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (provider_id) REFERENCES integration_providers(id) ON DELETE CASCADE,
    UNIQUE KEY uk_endpoint (endpoint_path)
);
```

#### `integration_queue`
Offline/retry queue.
```sql
CREATE TABLE integration_queue (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    provider_id INT NOT NULL,
    operation VARCHAR(100) NOT NULL,
    payload JSON NOT NULL,
    priority INT DEFAULT 0,
    attempts INT DEFAULT 0,
    max_attempts INT DEFAULT 5,
    status ENUM('pending', 'processing', 'completed', 'failed', 'cancelled') DEFAULT 'pending',
    next_attempt_at DATETIME,
    last_attempt_at DATETIME,
    error_log JSON,
    completed_at DATETIME,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (provider_id) REFERENCES integration_providers(id) ON DELETE CASCADE,
    INDEX idx_status_next (status, next_attempt_at),
    INDEX idx_school (school_id)
);
```

#### `oauth_tokens`
OAuth authentication tokens.
```sql
CREATE TABLE oauth_tokens (
    id INT PRIMARY KEY AUTO_INCREMENT,
    school_id INT NOT NULL,
    provider_id INT NOT NULL,
    access_token TEXT NOT NULL,
    refresh_token TEXT,
    token_type VARCHAR(50) DEFAULT 'Bearer',
    expires_at DATETIME,
    scopes JSON,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (provider_id) REFERENCES integration_providers(id) ON DELETE CASCADE,
    UNIQUE KEY uk_school_provider (school_id, provider_id)
);
```

### 2.2 Adapter Architecture

#### Base Adapter Interface
```php
<?php
namespace App\Modules\Integrations\Contracts;

interface IntegrationAdapterInterface
{
    public function getCategory(): string;
    public function getCode(): string;
    public function configure(array $config): void;
    public function testConnection(): bool;
    public function getRequiredConfig(): array;
}
```

#### Payment Adapter Interface
```php
<?php
namespace App\Modules\Integrations\Contracts;

interface PaymentAdapterInterface extends IntegrationAdapterInterface
{
    public function initiatePayment(PaymentRequest $request): PaymentResponse;
    public function verifyPayment(string $transactionId): PaymentStatus;
    public function handleCallback(array $payload): PaymentCallback;
    public function refund(string $transactionId, float $amount): RefundResponse;
}
```

#### SMS Adapter Interface
```php
<?php
namespace App\Modules\Integrations\Contracts;

interface SmsAdapterInterface extends IntegrationAdapterInterface
{
    public function send(string $to, string $message, array $options = []): SmsResponse;
    public function sendBulk(array $recipients, string $message): BulkSmsResponse;
    public function getBalance(): float;
    public function getDeliveryStatus(string $messageId): DeliveryStatus;
}
```

### 2.3 Available Adapters

| Category | Provider | Adapter Class | Status |
|:---------|:---------|:--------------|:-------|
| Payment | M-Pesa | `MpesaAdapter` | ✅ Implemented |
| Payment | Flutterwave | `FlutterwaveAdapter` | ✅ Implemented |
| Payment | Pesapal | `PesapalAdapter` | 🔄 Planned |
| SMS | Africa's Talking | `AfricasTalkingAdapter` | ✅ Implemented |
| SMS | Twilio | `TwilioAdapter` | 🔄 Planned |
| Email | SMTP | `SmtpAdapter` | ✅ Implemented |
| Email | SendGrid | `SendGridAdapter` | 🔄 Planned |
| Email | Mailgun | `MailgunAdapter` | 🔄 Planned |
| Push | FCM | `FcmAdapter` | 🔄 Planned |
| Push | APNs | `ApnsAdapter` | 🔄 Planned |
| WhatsApp | WhatsApp Business | `WhatsAppAdapter` | 🔄 Planned |
| Storage | Local | `LocalStorageAdapter` | ✅ Implemented |
| Storage | Google Drive | `GoogleDriveAdapter` | 🔄 Planned |
| Storage | S3 | `S3Adapter` | 🔄 Planned |
| LMS | Moodle | `MoodleAdapter` | 🔄 Planned |

### 2.4 API Endpoints

| Method | Endpoint | Description | Access |
|:-------|:---------|:------------|:-------|
| **Providers** |
| GET | `/api/v1/integrations/providers` | List providers | Admin |
| GET | `/api/v1/integrations/providers/{category}` | Providers by category | Admin |
| **Configuration** |
| GET | `/api/v1/integrations/configs` | List school configs | Admin |
| POST | `/api/v1/integrations/configs` | Create config | Admin |
| PUT | `/api/v1/integrations/configs/{id}` | Update config | Admin |
| POST | `/api/v1/integrations/configs/{id}/test` | Test connection | Admin |
| **Logs** |
| GET | `/api/v1/integrations/logs` | View logs | Admin |
| GET | `/api/v1/integrations/logs/{id}` | Log details | Admin |
| **Queue** |
| GET | `/api/v1/integrations/queue` | View queue | Admin |
| POST | `/api/v1/integrations/queue/{id}/retry` | Retry item | Admin |
| DELETE | `/api/v1/integrations/queue/{id}` | Cancel item | Admin |
| **Webhooks** |
| POST | `/webhooks/mpesa/callback` | M-Pesa callback | Public |
| POST | `/webhooks/mpesa/validation` | M-Pesa validation | Public |
| POST | `/webhooks/flutterwave` | Flutterwave callback | Public |
| POST | `/webhooks/sms/delivery` | SMS delivery report | Public |
| **Operations** |
| POST | `/api/v1/integrations/sms/send` | Send SMS | Staff |
| POST | `/api/v1/integrations/email/send` | Send Email | Staff |
| POST | `/api/v1/integrations/push/send` | Send Push | Staff |

### 2.5 Module Structure

```
app/Modules/Integrations/
├── Config/
│   ├── Routes.php
│   └── Services.php
├── Contracts/
│   ├── IntegrationAdapterInterface.php
│   ├── PaymentAdapterInterface.php
│   ├── SmsAdapterInterface.php
│   ├── EmailAdapterInterface.php
│   ├── PushAdapterInterface.php
│   ├── WhatsAppAdapterInterface.php
│   └── StorageAdapterInterface.php
├── Controllers/
│   ├── Api/
│   │   ├── ProviderController.php
│   │   ├── ConfigController.php
│   │   ├── LogController.php
│   │   └── QueueController.php
│   ├── Webhooks/
│   │   ├── MpesaWebhookController.php
│   │   ├── FlutterwaveWebhookController.php
│   │   └── SmsWebhookController.php
│   └── Web/
│       └── IntegrationsDashboardController.php
├── Adapters/
│   ├── Payment/
│   │   ├── MpesaAdapter.php
│   │   ├── FlutterwaveAdapter.php
│   │   └── PesapalAdapter.php
│   ├── Sms/
│   │   ├── AfricasTalkingAdapter.php
│   │   └── TwilioAdapter.php
│   ├── Email/
│   │   ├── SmtpAdapter.php
│   │   ├── SendGridAdapter.php
│   │   └── MailgunAdapter.php
│   ├── Push/
│   │   ├── FcmAdapter.php
│   │   └── ApnsAdapter.php
│   ├── WhatsApp/
│   │   └── WhatsAppBusinessAdapter.php
│   └── Storage/
│       ├── LocalStorageAdapter.php
│       ├── GoogleDriveAdapter.php
│       └── S3Adapter.php
├── Models/
│   ├── IntegrationProviderModel.php
│   ├── IntegrationConfigModel.php
│   ├── IntegrationLogModel.php
│   ├── IntegrationWebhookModel.php
│   ├── IntegrationQueueModel.php
│   └── OAuthTokenModel.php
├── Services/
│   ├── IntegrationService.php
│   ├── AdapterFactory.php
│   ├── WebhookProcessor.php
│   ├── QueueProcessor.php
│   ├── OAuthService.php
│   └── ConfigEncryptionService.php
├── DTOs/
│   ├── PaymentRequest.php
│   ├── PaymentResponse.php
│   ├── SmsResponse.php
│   └── ...
├── Jobs/
│   ├── ProcessIntegrationQueueJob.php
│   └── RefreshOAuthTokensJob.php
├── Database/
│   └── Migrations/
│       └── 2025-11-27-000001_CreateIntegrationsTables.php
├── Views/
│   ├── dashboard/
│   ├── configs/
│   └── logs/
└── Tests/
    ├── Unit/
    │   └── MpesaAdapterTest.php
    └── Feature/
        └── IntegrationsApiTest.php
```

### 2.6 Integration Points

- **Finance Module**: Payment processing for invoices.
- **Wallets Module**: Top-up via M-Pesa.
- **Threads Module**: SMS, Email, Push delivery.
- **Reports Module**: Scheduled report email delivery.
- **Scheduler Module**: Queue processing jobs.
- **Foundation Module**: Audit logging.

---

## Part 3: Architectural Safeguards
*Target Audience: Architects, Security Engineers*

### 3.1 Credential Security
- All credentials encrypted at rest (AES-256).
- Decrypt only when needed for API calls.
- Never log sensitive credentials.
- Rotate encryption keys periodically.

### 3.2 Webhook Security
- Validate signatures for each provider.
- Use HTTPS only.
- Implement replay protection (nonce/timestamp).
- IP whitelisting where supported.

### 3.3 Rate Limiting
- Respect provider rate limits.
- Queue overflow requests.
- Implement circuit breaker pattern.
- Monitor and alert on failures.

### 3.4 Idempotency
- Use transaction references to prevent duplicates.
- Check existing transactions before processing.
- Return existing result for duplicate requests.

### 3.5 Audit Trail
- Log all API calls with request/response.
- Mask sensitive data in logs.
- Retain logs per compliance requirements.

---

## Part 4: Test Data Strategy

### 4.1 Seeding Strategy
- All providers registered.
- Sandbox configs for test schools.
- Sample logs and queue items.

### 4.2 Testing Scenarios
| Scenario | Expected Outcome |
|:---------|:-----------------|
| M-Pesa STK Push | Callback received, payment recorded |
| SMS send | Message delivered, status logged |
| Invalid webhook signature | Request rejected |
| Queue retry after failure | Item reprocessed |
| OAuth token expiry | Auto-refresh triggered |

---

## Part 5: Development Checklist

- [x] **Architecture**: Adapter interfaces defined.
- [x] **M-Pesa**: STK Push and C2B working.
- [x] **SMS**: Africa's Talking integrated.
- [ ] **Email**: SMTP adapter complete.
- [ ] **Email**: SendGrid adapter.
- [ ] **Push**: FCM adapter.
- [ ] **WhatsApp**: Business API adapter.
- [ ] **Storage**: Google Drive adapter.
- [x] **Queue**: Offline retry working.
- [ ] **OAuth**: Token refresh automation.
