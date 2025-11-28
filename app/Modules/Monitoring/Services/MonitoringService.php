<?php

namespace App\Modules\Monitoring\Services;

use CodeIgniter\Database\ConnectionInterface;
use Config\Database;

/**
 * MonitoringService - Handles health checks, metrics, and observability.
 */
class MonitoringService
{
    private $db;

    public function __construct(?ConnectionInterface $connection = null)
    {
        $this->db = $connection ?? Database::connect();
    }

    /**
     * Run all active health checks.
     */
    public function runHealthChecks(): array
    {
        $checks = $this->db->table('health_checks')
            ->where('is_active', 1)
            ->get()
            ->getResultArray();

        $results = [];
        foreach ($checks as $check) {
            $result = $this->runCheck($check);
            $results[$check['name']] = $result;

            // Store result
            $this->db->table('health_check_results')->insert([
                'health_check_id' => $check['id'],
                'status' => $result['status'],
                'response_time_ms' => $result['response_time_ms'],
                'message' => $result['message'],
                'details' => json_encode($result['details'] ?? []),
                'checked_at' => date('Y-m-d H:i:s'),
            ]);
        }

        return $results;
    }

    /**
     * Run a single health check.
     */
    private function runCheck(array $check): array
    {
        $startTime = microtime(true);

        try {
            $result = match ($check['check_type']) {
                'database' => $this->checkDatabase(),
                'cache' => $this->checkCache(),
                'queue' => $this->checkQueue(),
                'storage' => $this->checkStorage(),
                'external_api' => $this->checkExternalApi($check['endpoint'], $check['timeout_seconds']),
                default => ['status' => 'healthy', 'message' => 'Check not implemented'],
            };
        } catch (\Exception $e) {
            $result = [
                'status' => 'unhealthy',
                'message' => $e->getMessage(),
            ];
        }

        $result['response_time_ms'] = (int) ((microtime(true) - $startTime) * 1000);

        return $result;
    }

    /**
     * Check database connectivity.
     */
    private function checkDatabase(): array
    {
        $this->db->query('SELECT 1');
        return [
            'status' => 'healthy',
            'message' => 'Database connection successful',
        ];
    }

    /**
     * Check cache connectivity.
     */
    private function checkCache(): array
    {
        $cache = service('cache');
        $testKey = 'health_check_' . time();
        $cache->save($testKey, 'test', 10);
        $value = $cache->get($testKey);
        $cache->delete($testKey);

        if ($value === 'test') {
            return ['status' => 'healthy', 'message' => 'Cache working'];
        }

        return ['status' => 'degraded', 'message' => 'Cache read/write failed'];
    }

    /**
     * Check job queue status.
     */
    private function checkQueue(): array
    {
        $pending = $this->db->table('job_queue')
            ->where('reserved_at IS NULL')
            ->countAllResults();

        $failed = $this->db->table('job_failed')
            ->where('failed_at >=', date('Y-m-d', strtotime('-1 day')))
            ->countAllResults();

        $status = $failed > 10 ? 'degraded' : 'healthy';

        return [
            'status' => $status,
            'message' => "Pending: {$pending}, Failed (24h): {$failed}",
            'details' => ['pending' => $pending, 'failed_24h' => $failed],
        ];
    }

    /**
     * Check storage availability.
     */
    private function checkStorage(): array
    {
        $path = WRITEPATH;
        $freeSpace = disk_free_space($path);
        $totalSpace = disk_total_space($path);
        $usedPercent = (($totalSpace - $freeSpace) / $totalSpace) * 100;

        $status = $usedPercent > 90 ? 'unhealthy' : ($usedPercent > 80 ? 'degraded' : 'healthy');

        return [
            'status' => $status,
            'message' => sprintf('Disk usage: %.1f%%', $usedPercent),
            'details' => [
                'free_gb' => round($freeSpace / 1073741824, 2),
                'total_gb' => round($totalSpace / 1073741824, 2),
                'used_percent' => round($usedPercent, 2),
            ],
        ];
    }

    /**
     * Check external API endpoint.
     */
    private function checkExternalApi(string $endpoint, int $timeout): array
    {
        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['status' => 'unhealthy', 'message' => $error];
        }

        $status = ($httpCode >= 200 && $httpCode < 300) ? 'healthy' : 'unhealthy';

        return [
            'status' => $status,
            'message' => "HTTP {$httpCode}",
            'details' => ['http_code' => $httpCode],
        ];
    }

    /**
     * Record a metric.
     */
    public function recordMetric(string $name, float $value, string $type = 'gauge', ?array $labels = null, ?int $schoolId = null): void
    {
        $this->db->table('metrics')->insert([
            'school_id' => $schoolId ?? session('school_id'),
            'metric_name' => $name,
            'metric_type' => $type,
            'value' => $value,
            'labels' => $labels ? json_encode($labels) : null,
            'recorded_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Increment a counter metric.
     */
    public function incrementCounter(string $name, float $increment = 1, ?array $labels = null): void
    {
        $this->recordMetric($name, $increment, 'counter', $labels);
    }

    /**
     * Log a request for performance tracking.
     */
    public function logRequest(string $method, string $path, int $statusCode, int $durationMs, ?string $traceId = null, ?int $userId = null, ?string $ipAddress = null): void
    {
        $this->db->table('request_logs')->insert([
            'school_id' => session('school_id'),
            'trace_id' => $traceId ?? $this->generateTraceId(),
            'method' => $method,
            'path' => $path,
            'status_code' => $statusCode,
            'duration_ms' => $durationMs,
            'user_id' => $userId ?? session('user_id'),
            'ip_address' => $ipAddress ?? service('request')->getIPAddress(),
            'user_agent' => service('request')->getUserAgent()->getAgentString(),
            'recorded_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Log an error.
     */
    public function logError(string $level, string $message, ?\Throwable $exception = null, ?array $context = null, ?string $traceId = null): void
    {
        $fingerprint = $this->generateErrorFingerprint($message, $exception);

        // Check for existing error with same fingerprint
        $existing = $this->db->table('error_logs')
            ->where('fingerprint', $fingerprint)
            ->where('resolved_at IS NULL')
            ->get()
            ->getRowArray();

        if ($existing) {
            $this->db->table('error_logs')
                ->where('id', $existing['id'])
                ->set('occurrence_count', 'occurrence_count + 1', false)
                ->set('last_seen_at', date('Y-m-d H:i:s'))
                ->update();
            return;
        }

        $this->db->table('error_logs')->insert([
            'school_id' => session('school_id'),
            'trace_id' => $traceId,
            'level' => $level,
            'message' => $message,
            'exception_class' => $exception ? get_class($exception) : null,
            'file' => $exception?->getFile(),
            'line' => $exception?->getLine(),
            'stack_trace' => $exception?->getTraceAsString(),
            'context' => $context ? json_encode($context) : null,
            'user_id' => session('user_id'),
            'fingerprint' => $fingerprint,
            'first_seen_at' => date('Y-m-d H:i:s'),
            'last_seen_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Get system health summary.
     */
    public function getHealthSummary(): array
    {
        $latest = $this->db->table('health_check_results hcr')
            ->select('hc.name, hc.is_critical, hcr.status, hcr.response_time_ms, hcr.message, hcr.checked_at')
            ->join('health_checks hc', 'hc.id = hcr.health_check_id')
            ->where('hcr.checked_at >=', date('Y-m-d H:i:s', strtotime('-5 minutes')))
            ->orderBy('hcr.checked_at', 'DESC')
            ->get()
            ->getResultArray();

        $summary = [
            'overall_status' => 'healthy',
            'checks' => [],
            'critical_issues' => 0,
        ];

        $seen = [];
        foreach ($latest as $result) {
            if (isset($seen[$result['name']])) continue;
            $seen[$result['name']] = true;

            $summary['checks'][$result['name']] = $result;

            if ($result['status'] === 'unhealthy') {
                $summary['overall_status'] = 'unhealthy';
                if ($result['is_critical']) {
                    $summary['critical_issues']++;
                }
            } elseif ($result['status'] === 'degraded' && $summary['overall_status'] !== 'unhealthy') {
                $summary['overall_status'] = 'degraded';
            }
        }

        return $summary;
    }

    /**
     * Generate a trace ID.
     */
    private function generateTraceId(): string
    {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    /**
     * Generate error fingerprint for deduplication.
     */
    private function generateErrorFingerprint(string $message, ?\Throwable $exception): string
    {
        $data = $message;
        if ($exception) {
            $data .= get_class($exception) . $exception->getFile() . $exception->getLine();
        }
        return hash('sha256', $data);
    }
}
