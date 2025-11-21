<?php

declare(strict_types=1);

namespace Modules\Hr\Services;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\I18n\Time;
use Config\Database;
use InvalidArgumentException;
use JsonException;
use Modules\Foundation\Services\AuditService;
use Modules\Foundation\Services\MakerCheckerService;
use RuntimeException;

/**
 * Provides read/write helpers around payroll approval requests.
 */
class PayrollApprovalService
{
    /**
     * @phpstan-var BaseConnection<object, object>
     */
    private BaseConnection $db;

    /**
     * @param BaseConnection<object, object>|null $connection
     */
    public function __construct(
        ?BaseConnection $connection = null,
        private ?MakerCheckerService $makerChecker = null,
        ?AuditService $auditService = null,
    ) {
        $this->db = $connection ?? Database::connect();
        $this->makerChecker ??= new MakerCheckerService($this->db, $auditService ?? new AuditService($this->db));
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function listPending(?string $tenantId = null): array
    {
        $builder = $this->db->table('ci4_maker_checker_requests');
        $builder->where('action_key', 'payroll.payslip');
        $builder->where('status', 'pending');

        if ($tenantId !== null && $tenantId !== '') {
            $builder->where('tenant_id', $tenantId);
        }

        $builder->orderBy('submitted_at', 'DESC');

        $rows = $builder->get()->getResultArray();

        return array_map(fn (array $row): array => $this->formatRow($row), $rows);
    }

    /**
     * @return array<string, mixed>
     */
    public function summarise(?string $tenantId = null): array
    {
        $builder = $this->db->table('ci4_maker_checker_requests');
        $builder->select('status, COUNT(*) AS total');
        $builder->where('action_key', 'payroll.payslip');

        if ($tenantId !== null && $tenantId !== '') {
            $builder->where('tenant_id', $tenantId);
        }

        $builder->groupBy('status');

        $summary = [
            'pending'  => 0,
            'approved' => 0,
            'rejected' => 0,
        ];

        foreach ($builder->get()->getResultArray() as $row) {
            $status = strtolower((string) $row['status']);
            if (array_key_exists($status, $summary)) {
                $summary[$status] = (int) $row['total'];
            }
        }

        return [
            'counts'     => $summary,
            'updated_at' => Time::now('UTC')->toDateTimeString(),
        ];
    }

    /**
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    public function approve(int $requestId, array $context): array
    {
        $this->makerChecker->approve($requestId, $context);

        return $this->fetchAndFormat($requestId);
    }

    /**
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    public function reject(int $requestId, string $reason, array $context): array
    {
        $reason = trim($reason);
        if ($reason === '') {
            throw new InvalidArgumentException('Rejection reason is required.');
        }

        $this->makerChecker->reject($requestId, $context, $reason);

        return $this->fetchAndFormat($requestId);
    }

    /**
     * @return array<string, mixed>
     */
    private function fetchAndFormat(int $requestId): array
    {
        $row = $this->db->table('ci4_maker_checker_requests')
            ->where('id', $requestId)
            ->get()
            ->getFirstRow('array');

        if (! $row) {
            throw new RuntimeException('Maker checker request not found.');
        }

        return $this->formatRow($row);
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function formatRow(array $row): array
    {
        $payload = [];

        try {
            if (! empty($row['payload_json'])) {
                $payload = json_decode((string) $row['payload_json'], true, 512, JSON_THROW_ON_ERROR);
            }
        } catch (JsonException $exception) {
            $payload = ['error' => 'Unable to decode payload: ' . $exception->getMessage()];
        }

        $statutory = [];
        if (isset($payload['statutory']) && is_array($payload['statutory'])) {
            $statutory = $payload['statutory'];
        }

        return [
            'id'               => (int) $row['id'],
            'tenant_id'        => $row['tenant_id'],
            'status'           => $row['status'],
            'maker_id'         => $row['maker_id'],
            'checker_id'       => $row['checker_id'],
            'rejection_reason' => $row['rejection_reason'],
            'submitted_at'     => $row['submitted_at'],
            'processed_at'     => $row['processed_at'],
            'employee'         => [
                'id'   => $payload['employee_id'] ?? null,
                'name' => $payload['employee_name'] ?? null,
            ],
            'period'           => $payload['period'] ?? null,
            'gross_pay'        => isset($payload['gross_pay']) ? (float) $payload['gross_pay'] : null,
            'net_pay'          => isset($payload['net_pay']) ? (float) $payload['net_pay'] : null,
            'statutory'        => $statutory,
            'payload'          => $payload,
        ];
    }
}
