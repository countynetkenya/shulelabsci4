<?php

namespace Modules\Integrations\Services\Interfaces;

/**
 * Interface for SMS gateway integrations.
 */
interface SMSGatewayInterface extends IntegrationAdapterInterface
{
    /**
     * Send an SMS message.
     *
     * @param array{to: string|array<string>, message: string, from?: string} $payload
     * @param array<string, mixed> $context
     * @return array{message_id: string, status: string, recipients?: int, cost?: float}
     */
    public function send(array $payload, array $context): array;

    /**
     * Query the delivery status of an SMS.
     *
     * @param string $messageId
     * @param array<string, mixed> $context
     * @return array{status: string, delivered_at?: string, error?: string}
     */
    public function queryStatus(string $messageId, array $context): array;

    /**
     * Get the account balance.
     *
     * @param array<string, mixed> $context
     * @return array{balance: float, currency: string}
     */
    public function getBalance(array $context): array;
}
