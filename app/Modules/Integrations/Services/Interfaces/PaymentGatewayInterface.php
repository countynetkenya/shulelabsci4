<?php

namespace Modules\Integrations\Services\Interfaces;

/**
 * Interface for payment gateway integrations.
 */
interface PaymentGatewayInterface extends IntegrationAdapterInterface
{
    /**
     * Initiate a payment charge.
     *
     * @param array{amount: float, currency: string, phone?: string, email?: string, reference?: string} $payload
     * @param array<string, mixed> $context
     * @return array{transaction_id: string, status: string, message?: string, redirect_url?: string}
     */
    public function charge(array $payload, array $context): array;

    /**
     * Query the status of a payment transaction.
     *
     * @param string $transactionId
     * @param array<string, mixed> $context
     * @return array{status: string, amount?: float, message?: string}
     */
    public function queryTransaction(string $transactionId, array $context): array;

    /**
     * Process a refund for a transaction.
     *
     * @param string $transactionId
     * @param float $amount
     * @param array<string, mixed> $context
     * @return array{refund_id: string, status: string, message?: string}
     */
    public function refund(string $transactionId, float $amount, array $context): array;
}
