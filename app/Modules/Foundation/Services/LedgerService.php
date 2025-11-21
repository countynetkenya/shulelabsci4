<?php

namespace Modules\Foundation\Services;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\ConnectionInterface;
use CodeIgniter\I18n\Time;
use Config\Database;
use RuntimeException;

/**
 * Append-only ledger orchestration with accounting period locks.
 */
class LedgerService
{
    /**
     * @phpstan-var BaseConnection<object, object>
     */
    private BaseConnection $db;
    private AuditService $auditService;

    /**
     * @phpstan-param ConnectionInterface<object, object>|null $connection
     */
    public function __construct(?ConnectionInterface $connection = null, ?AuditService $auditService = null)
    {
        $this->db           = $connection instanceof BaseConnection ? $connection : Database::connect();
        $this->auditService = $auditService ?? new AuditService($this->db);
    }

    /**
     * Commits a balanced double-entry journal in an append-only fashion.
     *
     * @param array<int, array<string, mixed>> $entries
     * @param array<string, mixed> $context
     * @param array<string, mixed> $metadata
     */
    public function commitTransaction(string $transactionKey, array $entries, array $context, array $metadata = []): int
    {
        if ($entries === []) {
            throw new RuntimeException('Ledger transaction requires at least one entry.');
        }

        $transactedAt = $this->resolveTransactionTime($context);
        $tenantId     = $context['tenant_id'] ?? null;

        $this->assertPeriodUnlocked($tenantId, $transactedAt);
        $this->assertBalanced($entries);

        $this->db->transStart();

        $transactionId = $this->insertTransaction($transactionKey, $context, $metadata, $transactedAt);
        foreach ($entries as $entry) {
            $this->insertEntry($transactionId, $entry);
        }

        $this->auditService->recordEvent(
            eventKey: $transactionKey,
            eventType: 'ledger_transaction_committed',
            context: $context,
            before: null,
            after: [
                'transaction_id' => $transactionId,
                'entries'        => $entries,
                'metadata'       => $metadata,
                'transacted_at'  => $transactedAt->toDateTimeString(),
            ],
            metadata: $metadata
        );

        $this->db->transComplete();

        if (! $this->db->transStatus()) {
            throw new RuntimeException('Failed to commit ledger transaction.');
        }

        return $transactionId;
    }

    /**
     * Creates an append-only reversal entry referencing an earlier transaction.
     *
     * @param array<string, mixed> $context
     */
    public function scheduleReversal(int $transactionId, array $context, string $reason): int
    {
        $original = $this->db->table('ci4_ledger_transactions')
            ->where('id', $transactionId)
            ->get()
            ->getFirstRow('array');

        if (! $original) {
            throw new RuntimeException('Cannot reverse missing transaction.');
        }

        $entries = $this->db->table('ci4_ledger_entries')
            ->where('transaction_id', $transactionId)
            ->get()
            ->getResultArray();

        if ($entries === []) {
            throw new RuntimeException('Cannot reverse transaction without entries.');
        }

        $reversalEntries = array_map(
            static function (array $entry): array {
                $flippedDirection = $entry['direction'] === 'debit' ? 'credit' : 'debit';

                return [
                    'account_code' => $entry['account_code'],
                    'direction'    => $flippedDirection,
                    'amount'       => $entry['amount'],
                    'memo'         => 'Reversal entry',
                ];
            },
            $entries
        );

        $metadata = [
            'reversal_reason'      => $reason,
            'reversal_source_id'   => $transactionId,
        ];

        return $this->commitTransaction(
            transactionKey: sprintf('%s:reversal:%s', $original['transaction_key'], $transactionId),
            entries: $reversalEntries,
            context: $context,
            metadata: array_merge($metadata, $context['metadata'] ?? [])
        );
    }

    /**
     * @param array<int, array<string, mixed>> $entries
     */
    private function assertBalanced(array $entries): void
    {
        $totalDebit  = '0';
        $totalCredit = '0';

        foreach ($entries as $entry) {
            if (! isset($entry['direction'], $entry['amount'], $entry['account_code'])) {
                throw new RuntimeException('Ledger entry requires account_code, direction, and amount.');
            }

            $amount = $this->normaliseMoney($entry['amount']);

            if ($entry['direction'] === 'debit') {
                $totalDebit = bcadd($totalDebit, $amount, 4);
            } elseif ($entry['direction'] === 'credit') {
                $totalCredit = bcadd($totalCredit, $amount, 4);
            } else {
                throw new RuntimeException('Ledger entry direction must be debit or credit.');
            }
        }

        if (bccomp($totalDebit, $totalCredit, 4) !== 0) {
            throw new RuntimeException('Ledger transaction must balance.');
        }
    }

    private function assertPeriodUnlocked(null|int|string $tenantId, Time $transactedAt): void
    {
        $builder = $this->db->table('ci4_ledger_period_locks');
        if ($tenantId !== null) {
            $builder->groupStart()
                ->where('tenant_id', $tenantId)
                ->orWhere('tenant_id', null)
                ->groupEnd();
        }

        $builder->where('period_start <=', $transactedAt->toDateString());
        $builder->where('period_end >=', $transactedAt->toDateString());

        if ($builder->get()->getFirstRow()) {
            throw new RuntimeException('Accounting period is locked.');
        }
    }

    /**
     * @param array<string, mixed> $context
     * @param array<string, mixed> $metadata
     */
    private function insertTransaction(string $transactionKey, array $context, array $metadata, Time $transactedAt): int
    {
        $payload = [
            'transaction_key' => $transactionKey,
            'tenant_id'       => $context['tenant_id'] ?? null,
            'currency_code'   => $context['currency'] ?? 'KES',
            'transacted_at'   => $transactedAt->toDateTimeString(),
            'created_at'      => Time::now('UTC')->toDateTimeString(),
            'metadata_json'   => json_encode($metadata, JSON_THROW_ON_ERROR),
        ];

        $this->db->table('ci4_ledger_transactions')->insert($payload);

        return (int) $this->db->insertID();
    }

    /**
     * @param array<string, mixed> $entry
     */
    private function insertEntry(int $transactionId, array $entry): void
    {
        $payload = [
            'transaction_id' => $transactionId,
            'account_code'   => $entry['account_code'],
            'direction'      => $entry['direction'],
            'amount'         => $this->normaliseMoney($entry['amount']),
            'memo'           => $entry['memo'] ?? null,
            'created_at'     => Time::now('UTC')->toDateTimeString(),
        ];

        $this->db->table('ci4_ledger_entries')->insert($payload);
    }

    /**
     * @param array<string, mixed> $context
     */
    private function resolveTransactionTime(array $context): Time
    {
        if (isset($context['transacted_at'])) {
            return Time::parse($context['transacted_at'], 'UTC');
        }

        return Time::now('UTC');
    }

    private function normaliseMoney(int|float|string $amount): string
    {
        if (is_string($amount)) {
            return bcadd($amount, '0', 4);
        }

        return number_format((float) $amount, 4, '.', '');
    }
}
