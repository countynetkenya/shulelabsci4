<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Healthz extends CI_Controller
{
    public function index(): void
    {
        $this->output->set_content_type('application/json');

        $this->load->config('iniconfig');

        $checks = [
            'database' => $this->checkDatabase(),
        ];

        $status = 'ok';
        $httpStatus = 200;

        foreach ($checks as $check) {
            if ($check['status'] !== 'passing') {
                $status = 'degraded';
                $httpStatus = max($httpStatus, 503);
            }
        }

        $payload = [
            'status' => $status,
            'timestamp' => date(DATE_ATOM),
            'version' => config_item('ini_version'),
            'checks' => $checks,
        ];

        $this->output
            ->set_status_header($httpStatus)
            ->set_output(json_encode($payload, JSON_UNESCAPED_SLASHES));
    }

    protected function checkDatabase(): array
    {
        $start = microtime(true);

        try {
            $this->load->database();
            $this->db->query('SELECT 1');

            return [
                'status' => 'passing',
                'latency_ms' => round((microtime(true) - $start) * 1000, 2),
            ];
        } catch (\Throwable $exception) {
            log_message('error', 'Healthz database check failed: ' . $exception->getMessage());

            return [
                'status' => 'failing',
                'latency_ms' => round((microtime(true) - $start) * 1000, 2),
                'error' => 'Database connectivity check failed',
            ];
        }
    }
}
