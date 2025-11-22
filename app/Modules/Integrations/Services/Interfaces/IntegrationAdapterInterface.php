<?php

namespace Modules\Integrations\Services\Interfaces;

/**
 * Base interface that all integration adapters must implement.
 */
interface IntegrationAdapterInterface
{
    /**
     * Execute the integration operation.
     *
     * @param string $operation The operation to perform (e.g., 'send', 'charge', 'upload')
     * @param array<string, mixed> $payload The data payload for the operation
     * @param array<string, mixed> $context Additional context (tenant_id, user_id, etc.)
     * @return array<string, mixed> Response data from the integration
     */
    public function execute(string $operation, array $payload, array $context): array;

    /**
     * Check the health/status of the integration.
     *
     * @return array{status: string, message?: string, details?: array<string, mixed>}
     */
    public function checkStatus(): array;

    /**
     * Get the unique identifier for this adapter.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Validate the configuration for this adapter.
     *
     * @param array<string, mixed> $config
     * @return bool True if configuration is valid
     */
    public function validateConfig(array $config): bool;
}
