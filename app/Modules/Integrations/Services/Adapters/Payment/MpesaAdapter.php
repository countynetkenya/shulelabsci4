<?php

namespace Modules\Integrations\Services\Adapters\Payment;

use Modules\Integrations\Services\Adapters\BaseAdapter;
use Modules\Integrations\Services\Interfaces\PaymentGatewayInterface;
use RuntimeException;

/**
 * M-Pesa payment gateway adapter.
 * Supports STK Push and C2B payment flows.
 */
class MpesaAdapter extends BaseAdapter implements PaymentGatewayInterface
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'mpesa';
    }

    /**
     * {@inheritdoc}
     */
    public function execute(string $operation, array $payload, array $context): array
    {
        return match ($operation) {
            'charge' => $this->charge($payload, $context),
            'query' => $this->queryTransaction($payload['transaction_id'] ?? '', $context),
            'refund' => $this->refund($payload['transaction_id'] ?? '', $payload['amount'] ?? 0.0, $context),
            default => throw new RuntimeException("Unknown operation: {$operation}"),
        };
    }

    /**
     * {@inheritdoc}
     */
    public function charge(array $payload, array $context): array
    {
        $this->log('info', 'Initiating M-Pesa STK Push', ['payload' => $payload]);

        // TODO: Implement actual M-Pesa STK Push API call
        // For now, return a stub response
        return [
            'transaction_id' => 'MPESA' . time(),
            'status'         => 'pending',
            'message'        => 'STK Push sent to customer phone',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function queryTransaction(string $transactionId, array $context): array
    {
        $this->log('info', 'Querying M-Pesa transaction', ['transaction_id' => $transactionId]);

        // TODO: Implement actual M-Pesa transaction query
        return [
            'status'  => 'completed',
            'amount'  => 1000.0,
            'message' => 'Transaction completed successfully',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function refund(string $transactionId, float $amount, array $context): array
    {
        $this->log('info', 'Processing M-Pesa refund', [
            'transaction_id' => $transactionId,
            'amount'         => $amount,
        ]);

        // TODO: Implement actual M-Pesa refund
        return [
            'refund_id' => 'REFUND' . time(),
            'status'    => 'pending',
            'message'   => 'Refund request submitted',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function checkStatus(): array
    {
        // TODO: Implement actual health check (e.g., verify credentials)
        return [
            'status'  => 'ok',
            'message' => 'M-Pesa adapter is operational',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getRequiredConfigKeys(): array
    {
        return ['consumer_key', 'consumer_secret', 'shortcode', 'passkey', 'environment'];
    }
}
