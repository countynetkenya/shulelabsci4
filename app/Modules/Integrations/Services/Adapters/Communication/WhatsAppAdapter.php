<?php

namespace Modules\Integrations\Services\Adapters\Communication;

use Modules\Integrations\Services\Adapters\BaseAdapter;
use RuntimeException;

/**
 * WhatsApp Business API adapter.
 */
class WhatsAppAdapter extends BaseAdapter
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'whatsapp';
    }

    /**
     * {@inheritdoc}
     */
    public function execute(string $operation, array $payload, array $context): array
    {
        return match ($operation) {
            'send' => $this->sendMessage($payload, $context),
            'send_template' => $this->sendTemplate($payload, $context),
            'query' => $this->queryStatus($payload['message_id'] ?? '', $context),
            default => throw new RuntimeException("Unknown operation: {$operation}"),
        };
    }

    /**
     * Send a WhatsApp message.
     *
     * @param array{to: string, message: string, type?: string} $payload
     * @param array<string, mixed> $context
     * @return array{message_id: string, status: string}
     */
    public function sendMessage(array $payload, array $context): array
    {
        $this->log('info', 'Sending WhatsApp message', ['payload' => $payload]);

        // TODO: Implement actual WhatsApp Business API call
        return [
            'message_id' => 'WA' . time(),
            'status'     => 'sent',
        ];
    }

    /**
     * Send a WhatsApp template message.
     *
     * @param array{to: string, template: string, params?: array<string>} $payload
     * @param array<string, mixed> $context
     * @return array{message_id: string, status: string}
     */
    public function sendTemplate(array $payload, array $context): array
    {
        $this->log('info', 'Sending WhatsApp template', ['payload' => $payload]);

        // TODO: Implement template message
        return [
            'message_id' => 'WA_TMPL' . time(),
            'status'     => 'sent',
        ];
    }

    /**
     * Query message status.
     *
     * @param string $messageId
     * @param array<string, mixed> $context
     * @return array{status: string, delivered_at?: string}
     */
    public function queryStatus(string $messageId, array $context): array
    {
        $this->log('info', 'Querying WhatsApp message status', ['message_id' => $messageId]);

        // TODO: Implement status query
        return [
            'status'       => 'delivered',
            'delivered_at' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function checkStatus(): array
    {
        return [
            'status'  => 'ok',
            'message' => 'WhatsApp adapter is operational',
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getRequiredConfigKeys(): array
    {
        return ['phone_number_id', 'access_token'];
    }
}
