<?php

namespace Modules\Integrations\Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Integration configuration.
 * Maps logical integration names to adapter classes and their settings.
 */
class Integrations extends BaseConfig
{
    /**
     * Global timeout for integration requests (in seconds).
     */
    public int $defaultTimeout = 30;

    /**
     * Maximum number of retry attempts for failed integrations.
     */
    public int $maxRetries = 3;

    /**
     * Retry backoff strategy: 'fixed', 'exponential', 'linear'
     */
    public string $retryStrategy = 'exponential';

    /**
     * Base delay for retries (in seconds).
     */
    public int $retryBaseDelay = 60;

    /**
     * Enable/disable webhook signature verification.
     */
    public bool $verifyWebhookSignatures = true;

    /**
     * Enable/disable request/response compression for mobile.
     */
    public bool $compressResponses = true;

    /**
     * Enable/disable local caching of integration responses.
     */
    public bool $cacheLocally = true;

    /**
     * Cache TTL for integration responses (in seconds).
     */
    public int $cacheTtl = 300;

    /**
     * Enable/disable offline sync queue.
     */
    public bool $enableOfflineSync = true;

    /**
     * Integration adapter mappings.
     * Format: 'logical_name' => ['adapter' => AdapterClass::class, 'enabled' => true]
     *
     * @var array<string, array{adapter: class-string, enabled: bool, timeout?: int}>
     */
    public array $adapters = [
        // Payment Gateways
        'mpesa' => [
            'adapter' => \Modules\Integrations\Services\Adapters\Payment\MpesaAdapter::class,
            'enabled' => false,
        ],
        'flutterwave' => [
            'adapter' => \Modules\Integrations\Services\Adapters\Payment\FlutterwaveAdapter::class,
            'enabled' => false,
        ],
        'pesapal' => [
            'adapter' => \Modules\Integrations\Services\Adapters\Payment\PesapalAdapter::class,
            'enabled' => false,
        ],

        // Communication
        'sms' => [
            'adapter' => \Modules\Integrations\Services\Adapters\Communication\SmsAdapter::class,
            'enabled' => false,
        ],
        'whatsapp' => [
            'adapter' => \Modules\Integrations\Services\Adapters\Communication\WhatsAppAdapter::class,
            'enabled' => false,
        ],

        // Storage
        'google_drive' => [
            'adapter' => \Modules\Integrations\Services\Adapters\Storage\GoogleDriveAdapter::class,
            'enabled' => false,
        ],
        'local_storage' => [
            'adapter' => \Modules\Integrations\Services\Adapters\Storage\LocalStorageAdapter::class,
            'enabled' => true,
        ],

        // LMS
        'moodle' => [
            'adapter' => \Modules\Integrations\Services\Adapters\LMS\MoodleAdapter::class,
            'enabled' => false,
        ],
    ];

    /**
     * Progressive notification fallback order.
     * When one channel fails, fall back to the next.
     *
     * @var array<string>
     */
    public array $notificationFallbackOrder = ['push', 'whatsapp', 'sms', 'email'];

    /**
     * Get adapter configuration for a specific integration.
     *
     * @param string $name
     * @return array{adapter: class-string, enabled: bool, timeout?: int}|null
     */
    public function getAdapterConfig(string $name): ?array
    {
        return $this->adapters[$name] ?? null;
    }

    /**
     * Check if an integration is enabled.
     *
     * @param string $name
     * @return bool
     */
    public function isEnabled(string $name): bool
    {
        $config = $this->getAdapterConfig($name);

        return $config !== null && ($config['enabled'] ?? false);
    }
}
