<?php
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;

class JwtMiddleware
{
    public function handle()
    {
        if (is_cli()) {
            return;
        }

        $CI =& get_instance();
        if (!isset($CI->uri) || !isset($CI->input)) {
            return;
        }

        $shulelabsConfig = $CI->config->item('shulelabs');
        $guardEnabled = isset($shulelabsConfig['security']['api_jwt_guard']['enabled'])
            ? (bool) $shulelabsConfig['security']['api_jwt_guard']['enabled']
            : false;
        if (!$guardEnabled) {
            return;
        }

        if (strtoupper($CI->input->method(TRUE)) === 'OPTIONS') {
            return;
        }

        $firstSegment = strtolower((string) $CI->uri->segment(1));
        if ($firstSegment !== 'api') {
            return;
        }

        $CI->load->library('Jwt_service');
        $header = $CI->input->get_request_header('Authorization', TRUE);
        $token = $CI->jwt_service->extractToken($header);

        if ($token === null) {
            // Support legacy token query param fallbacks.
            $token = $CI->input->get('token', TRUE);
            if (empty($token)) {
                $this->respondUnauthorized('Missing bearer token.');
            }
        }

        try {
            $claims = $CI->jwt_service->decode($token);
            $CI->jwt_claims = $claims;
        } catch (ExpiredException $exception) {
            $this->respondUnauthorized('Token expired. Please authenticate again.');
        } catch (SignatureInvalidException $exception) {
            $this->respondUnauthorized('Invalid token signature.');
        } catch (Throwable $exception) {
            $this->respondUnauthorized('Invalid token.');
        }
    }

    protected function respondUnauthorized($message)
    {
        $response = [
            'status' => false,
            'message' => $message,
        ];

        if (!headers_sent()) {
            header('Content-Type: application/json');
            header('HTTP/1.1 401 Unauthorized');
        }

        echo json_encode($response);
        exit;
    }
}
