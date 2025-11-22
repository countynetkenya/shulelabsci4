<?php

namespace Modules\Integrations\Services\Adapters\Communication;

use Modules\Integrations\Services\Adapters\BaseAdapter;
use Modules\Integrations\Services\Interfaces\SMSGatewayInterface;
use RuntimeException;

/**
 * SMS adapter using Africa's Talking.
 */
class SmsAdapter extends BaseAdapter implements SMSGatewayInterface
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'sms';
    }

    /**
     * {@inheritdoc}
     */
    public function execute(string $operation, array $payload, array $context): array
    {
        return match ($operation) {
            'send' => $this->send($payload, $context),
            'query' => $this->queryStatus($payload['message_id'] ?? '', $context),
            'balance' => $this->getBalance($context),
            default => throw new RuntimeException("Unknown operation: {$operation}"),
        };
    }

    /**
     * {@inheritdoc}
     */
    public function send(array $payload, array $context): array
    {
        $this->log('info', 'Sending SMS', ['payload' => $payload]);

        // TODO: Implement actual Africa's Talking API call
        return [
            'message_id' => 'SMS' . time(),
            'status'     => 'sent',
            'recipients' => is_array($payload['to']) ? count($payload['to']) : 1,
            'cost'       => 1.0,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function queryStatus(string $messageId, array $context): array
    {
        $this->log('info', 'Querying SMS status', ['message_id' => $messageId]);

        // TODO: Implement actual status query
        return [
            'status'       => 'delivered',
            'delivered_at' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getBalance(array $context): array
    {
        $this->log('info', 'Getting SMS balance');

        // TODO: Implement actual balance check
        return [
            'balance'  => 1000.0,
            'currency' => 'KES',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function checkStatus(): array
    {
        return [
            'status'  => 'ok',
            'message' => 'SMS adapter is operational',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getRequiredConfigKeys(): array
    {
        return ['username', 'api_key', 'sender_id'];
    }
}
