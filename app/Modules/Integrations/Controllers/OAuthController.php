<?php

namespace Modules\Integrations\Controllers;

use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\RESTful\ResourceController;

/**
 * OAuth callback controller.
 */
class OAuthController extends ResourceController
{
    protected $format = 'json';

    /**
     * Handle OAuth callback from external service.
     */
    public function callback(string $adapterName): ResponseInterface
    {
        $code  = $this->request->getGet('code');
        $state = $this->request->getGet('state');
        $error = $this->request->getGet('error');

        log_message('info', "OAuth callback for {$adapterName}", [
            'code'  => $code,
            'state' => $state,
            'error' => $error,
        ]);

        // TODO: Implement OAuth callback handling
        // 1. Verify state parameter
        // 2. Exchange code for access token
        // 3. Store token in integration_auth_tokens
        // 4. Redirect to success page

        return $this->respond([
            'status'  => 'success',
            'message' => 'OAuth callback received',
        ]);
    }
}
