<?php

namespace Modules\Integrations\Services\Adapters\Payment;

use Modules\Integrations\Services\Adapters\BaseAdapter;
use Modules\Integrations\Services\Interfaces\PaymentGatewayInterface;
use RuntimeException;

/**
 * Flutterwave payment gateway adapter.
 */
class FlutterwaveAdapter extends BaseAdapter implements PaymentGatewayInterface
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'flutterwave';
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
        $this->log('info', 'Initiating Flutterwave payment', ['payload' => $payload]);

        // TODO: Implement actual Flutterwave API call
        return [
            'transaction_id' => 'FLW' . time(),
            'status'         => 'pending',
            'message'        => 'Payment initiated',
            'redirect_url'   => 'https://checkout.flutterwave.com/example',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function queryTransaction(string $transactionId, array $context): array
    {
        $this->log('info', 'Querying Flutterwave transaction', ['transaction_id' => $transactionId]);

        // TODO: Implement actual transaction query
        return [
            'status'  => 'completed',
            'amount'  => 1000.0,
            'message' => 'Transaction successful',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function refund(string $transactionId, float $amount, array $context): array
    {
        $this->log('info', 'Processing Flutterwave refund', [
            'transaction_id' => $transactionId,
            'amount'         => $amount,
        ]);

        // TODO: Implement actual refund
        return [
            'refund_id' => 'REFUND' . time(),
            'status'    => 'pending',
            'message'   => 'Refund initiated',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function checkStatus(): array
    {
        return [
            'status'  => 'ok',
            'message' => 'Flutterwave adapter is operational',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getRequiredConfigKeys(): array
    {
        return ['public_key', 'secret_key', 'encryption_key'];
    }
}
