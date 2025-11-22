<?php

namespace Modules\Integrations\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

/**
 * Webhook receiver controller.
 */
class WebhookController extends ResourceController
{
    protected $format = 'json';

    /**
     * Receive webhook from external service.
     */
    public function receive(string $adapterName): ResponseInterface
    {
        $payload = $this->request->getJSON(true) ?? $this->request->getPost();

        log_message('info', "Webhook received for {$adapterName}", ['payload' => $payload]);

        // TODO: Implement webhook handling logic
        // 1. Verify signature
        // 2. Log to integration_webhook_logs
        // 3. Dispatch to appropriate handler
        // 4. Return 200 OK

        return $this->respond([
            'status'  => 'success',
            'message' => 'Webhook received',
        ]);
    }
}
