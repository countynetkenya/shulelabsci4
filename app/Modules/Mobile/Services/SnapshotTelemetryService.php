<?php

declare(strict_types=1);

namespace Modules\Mobile\Services;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\I18n\Time;
use Config\Database;
use InvalidArgumentException;
use JsonException;

/**
 * Aggregates telemetry for offline mobile snapshots using audit events.
 */
class SnapshotTelemetryService
{
    /**
     * @phpstan-var BaseConnection<object, object>
     */
    private BaseConnection $db;

    /**
     * @param BaseConnection<object, object>|null $connection
     */
    public function __construct(?BaseConnection $connection = null)
    {
        $this->db = $connection ?? Database::connect();
    }

    /**
     * @return array<string, mixed>
     */
    public function getTelemetry(int $hours = 24): array
    {
        if ($hours <= 0) {
            throw new InvalidArgumentException('Telemetry window must be positive.');
        }

        $end = Time::now('UTC');
        $start = (clone $end)->subHours($hours);

        $builder = $this->db->table('audit_events');
        $builder->where('created_at >=', $start->toDateTimeString());
        $builder->like('event_key', 'mobile.snapshot.', 'after');
        $builder->orderBy('created_at', 'DESC');

        $rows = $builder->get()->getResultArray();

        $totals = [
            'issued'   => 0,
            'verified' => 0,
            'failed'   => 0,
        ];

        /** @var array<string, array{issued: int, verified: int, failed: int}> $tenants */
        $tenants = [];
        $recentFailures = [];

        foreach ($rows as $row) {
            $eventType = (string) $row['event_type'];
            $metadata = $this->decodeJson($row['metadata_json'] ?? null);
            $after = $this->decodeJson($row['after_state'] ?? null);

            $tenantId = (string) ($metadata['tenant_id'] ?? $after['tenant_id'] ?? 'unknown');
            if ($tenantId === '') {
                $tenantId = 'unknown';
            }

            $tenants[$tenantId] ??= ['issued' => 0, 'verified' => 0, 'failed' => 0];

            if ($eventType === 'snapshot_issued') {
                $totals['issued']++;
                $tenants[$tenantId]['issued']++;
            } elseif ($eventType === 'snapshot_verified') {
                $totals['verified']++;
                $tenants[$tenantId]['verified']++;
            } elseif ($eventType === 'snapshot_verification_failed') {
                $totals['failed']++;
                $tenants[$tenantId]['failed']++;

                if (count($recentFailures) < 10) {
                    $recentFailures[] = [
                        'snapshot_id' => $after['snapshot_id'] ?? null,
                        'tenant_id'   => $tenantId,
                        'reason'      => $after['reason'] ?? $metadata['reason'] ?? 'unknown',
                        'occurred_at' => $row['created_at'],
                    ];
                }
            }
        }

        $tenantSummaries = [];
        foreach ($tenants as $tenantId => $counts) {
            $issued = max(1, $counts['issued']);
            $tenantSummaries[] = [
                'tenant_id'         => $tenantId,
                'issued'            => $counts['issued'],
                'verified'          => $counts['verified'],
                'failed'            => $counts['failed'],
                'verification_rate' => round($counts['verified'] / $issued, 2),
                'failure_rate'      => round($counts['failed'] / $issued, 2),
            ];
        }

        usort($tenantSummaries, static function (array $a, array $b): int {
            return $b['issued'] <=> $a['issued'];
        });

        return [
            'window' => [
                'start' => $start->toDateTimeString(),
                'end'   => $end->toDateTimeString(),
                'hours' => $hours,
            ],
            'totals'          => $totals,
            'tenants'         => $tenantSummaries,
            'recent_failures' => $recentFailures,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeJson(mixed $payload): array
    {
        if ($payload === null || $payload === '') {
            return [];
        }

        try {
            return json_decode((string) $payload, true, 512, JSON_THROW_ON_ERROR) ?? [];
        } catch (JsonException) {
            return [];
        }
    }
}
