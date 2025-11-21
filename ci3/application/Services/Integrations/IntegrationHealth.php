<?php

namespace App\Services\Integrations;

class IntegrationHealth
{
    /**
     * @param iterable<array{status?: string|null, latency_ms?: float|int|string|null}> $integrations
     * @return array{
     *     total: int,
     *     active: int,
     *     failing: int,
     *     disabled: int,
     *     latency_ms: array{p50: float, p95: float, p99: float},
     *     uptime_percentage: float
     * }
     */
    public function summarize(iterable $integrations): array
    {
        $latencies = [];
        $summary = [
            'total' => 0,
            'active' => 0,
            'failing' => 0,
            'disabled' => 0,
        ];

        foreach ($integrations as $integration) {
            $summary['total']++;
            $status  = strtolower((string) ($integration['status'] ?? 'disabled'));
            $latency = isset($integration['latency_ms']) ? (float) $integration['latency_ms'] : null;

            if ($status === 'active') {
                $summary['active']++;
            } elseif ($status === 'failing') {
                $summary['failing']++;
            } else {
                $summary['disabled']++;
            }

            if ($latency !== null) {
                $latencies[] = $latency;
            }
        }

        $uptime = $summary['total'] > 0
            ? round(($summary['active'] / $summary['total']) * 100, 2)
            : 0.0;

        return [
            'total' => $summary['total'],
            'active' => $summary['active'],
            'failing' => $summary['failing'],
            'disabled' => $summary['disabled'],
            'latency_ms' => $this->summarizeLatency($latencies),
            'uptime_percentage' => $uptime,
        ];
    }

    /**
     * @param list<float> $latencies
     * @return array{p50: float, p95: float, p99: float}
     */
    protected function summarizeLatency(array $latencies): array
    {
        if ($latencies === []) {
            return ['p50' => 0.0, 'p95' => 0.0, 'p99' => 0.0];
        }

        sort($latencies);
        $count = count($latencies);

        $percentile = static function (float $percent) use ($latencies, $count): float {
            $index = min($count - 1, max(0, (int) round(($percent / 100) * ($count - 1))));

            return round($latencies[$index], 2);
        };

        return [
            'p50' => $percentile(50),
            'p95' => $percentile(95),
            'p99' => $percentile(99),
        ];
    }
}
