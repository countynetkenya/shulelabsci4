<?php

namespace Modules\Integrations\Services;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\ConnectionInterface;
use Config\Database;
use Modules\Foundation\Services\AuditService;
use Modules\Foundation\Services\IntegrationRegistry;
use Modules\Integrations\Services\Interfaces\IntegrationAdapterInterface;
use RuntimeException;

/**
 * Central service for managing all third-party integrations.
 * Provides unified interface for executing integration operations with logging, retry, and events.
 */
class IntegrationService
{
    /**
     * @phpstan-var BaseConnection<object, object>
     */
    private BaseConnection $db;
    private AuditService $auditService;
    private IntegrationRegistry $registry;

    /**
     * @var array<string, IntegrationAdapterInterface>
     */
    private array $adapters = [];

    /**
     * @phpstan-param ConnectionInterface<object, object>|null $connection
     */
    public function __construct(
        ?ConnectionInterface $connection = null,
        ?AuditService $auditService = null,
        ?IntegrationRegistry $registry = null
    ) {
        $this->db           = $connection instanceof BaseConnection ? $connection : Database::connect();
        $this->auditService = $auditService ?? new AuditService($this->db);
        $this->registry     = $registry ?? new IntegrationRegistry($this->db, $this->auditService);
    }

    /**
     * Register an integration adapter.
     *
     * @param string $name Unique identifier for the adapter
     * @param IntegrationAdapterInterface $adapter The adapter instance
     */
    public function register(string $name, IntegrationAdapterInterface $adapter): void
    {
        $this->adapters[$name] = $adapter;
    }

    /**
     * Execute an integration operation with full logging and retry support.
     *
     * @param string $adapterName The name of the registered adapter
     * @param string $operation The operation to perform
     * @param array<string, mixed> $payload Operation-specific data
     * @param array<string, mixed> $context Additional context (tenant_id, user_id, etc.)
     * @return array<string, mixed> Response from the integration
     * @throws RuntimeException If adapter not found or execution fails
     */
    public function execute(string $adapterName, string $operation, array $payload, array $context = []): array
    {
        $adapter = $this->getAdapter($adapterName);

        // Generate idempotency key
        $idempotencyKey = $this->generateIdempotencyKey($adapterName, $operation, $payload);

        // Register the dispatch
        $dispatch = $this->registry->registerDispatch(
            $adapterName,
            $idempotencyKey,
            ['operation' => $operation, 'payload' => $payload],
            $context
        );

        // If already completed, return cached result
        if ($dispatch['status'] === 'completed') {
            return json_decode((string) $dispatch['response_json'], true, 512, JSON_THROW_ON_ERROR);
        }

        try {
            $response = $adapter->execute($operation, $payload, $context);

            $this->registry->markCompleted((int) $dispatch['id'], $context, $response);

            $this->auditService->recordEvent(
                eventKey: sprintf('integration:%s:success', $adapterName),
                eventType: 'integration_executed',
                context: array_merge($context, ['adapter' => $adapterName, 'operation' => $operation]),
                before: null,
                after: ['response' => $response]
            );

            return $response;
        } catch (\Throwable $e) {
            $errorMessage = sprintf('%s: %s', get_class($e), $e->getMessage());

            $this->registry->markFailed((int) $dispatch['id'], $context, $errorMessage, 300);

            $this->auditService->recordEvent(
                eventKey: sprintf('integration:%s:failure', $adapterName),
                eventType: 'integration_failed',
                context: array_merge($context, ['adapter' => $adapterName, 'operation' => $operation]),
                before: null,
                after: ['error' => $errorMessage]
            );

            throw new RuntimeException(
                sprintf('Integration "%s" operation "%s" failed: %s', $adapterName, $operation, $errorMessage),
                0,
                $e
            );
        }
    }

    /**
     * Get a registered adapter by name.
     *
     * @param string $name
     * @return IntegrationAdapterInterface
     * @throws RuntimeException If adapter not found
     */
    public function getAdapter(string $name): IntegrationAdapterInterface
    {
        if (! isset($this->adapters[$name])) {
            throw new RuntimeException(sprintf('Integration adapter "%s" not found', $name));
        }

        return $this->adapters[$name];
    }

    /**
     * Check if an adapter is registered.
     *
     * @param string $name
     * @return bool
     */
    public function hasAdapter(string $name): bool
    {
        return isset($this->adapters[$name]);
    }

    /**
     * Get all registered adapter names.
     *
     * @return array<string>
     */
    public function getRegisteredAdapters(): array
    {
        return array_keys($this->adapters);
    }

    /**
     * Check the health status of an integration.
     *
     * @param string $adapterName
     * @return array{status: string, message?: string, details?: array<string, mixed>}
     */
    public function checkHealth(string $adapterName): array
    {
        $adapter = $this->getAdapter($adapterName);

        try {
            return $adapter->checkStatus();
        } catch (\Throwable $e) {
            return [
                'status'  => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Generate an idempotency key for an operation.
     *
     * @param string $adapterName
     * @param string $operation
     * @param array<string, mixed> $payload
     * @return string
     */
    private function generateIdempotencyKey(string $adapterName, string $operation, array $payload): string
    {
        $data = sprintf('%s:%s:%s', $adapterName, $operation, json_encode($payload, JSON_THROW_ON_ERROR));

        return hash('sha256', $data);
    }
}
