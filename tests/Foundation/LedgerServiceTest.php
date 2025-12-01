<?php

declare(strict_types=1);

namespace Tests\Ci4\Foundation;

use Modules\Foundation\Services\AuditService;
use Modules\Foundation\Services\LedgerService;
use RuntimeException;

class LedgerServiceTest extends FoundationDatabaseTestCase
{
    public function testCommitTransactionPersistsLedgerAndAudit(): void
    {
        $audit = new AuditService($this->db);
        $ledger = new LedgerService($this->db, $audit);

        $transactionId = $ledger->commitTransaction(
            transactionKey: 'txn-1001',
            entries: [
                ['account_code' => '1000', 'direction' => 'debit', 'amount' => '150.00', 'memo' => 'Tuition invoice'],
                ['account_code' => '2000', 'direction' => 'credit', 'amount' => '150.00', 'memo' => 'Accounts receivable'],
            ],
            context: ['school_id' => 2, 'currency' => 'KES', 'transacted_at' => '2024-01-01 09:30:00'],
            metadata: ['source' => 'finance-module']
        );

        $this->assertGreaterThan(0, $transactionId);

        $transaction = $this->db->table('ledger_transactions')->where('id', $transactionId)->get()->getFirstRow('array');
        $this->assertSame('txn-1001', $transaction['transaction_key']);
        $this->assertSame('KES', $transaction['currency_code']);

        $entries = $this->db->table('ledger_entries')->where('transaction_id', $transactionId)->orderBy('id')->get()->getResultArray();
        $this->assertCount(2, $entries);
        $this->assertSame('debit', $entries[0]['direction']);
        $this->assertSame('credit', $entries[1]['direction']);

        $auditRow = $this->db->table('audit_events')
            ->where('event_type', 'ledger_transaction_committed')
            ->get()
            ->getFirstRow('array');

        $this->assertNotNull($auditRow);
        $this->assertStringContainsString('txn-1001', $auditRow['event_key']);
    }

    public function testCommitTransactionThrowsWhenUnbalanced(): void
    {
        $ledger = new LedgerService($this->db, new AuditService($this->db));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Ledger transaction must balance.');

        $ledger->commitTransaction(
            transactionKey: 'txn-imbalanced',
            entries: [
                ['account_code' => '1000', 'direction' => 'debit', 'amount' => '10.00'],
                ['account_code' => '2000', 'direction' => 'credit', 'amount' => '5.00'],
            ],
            context: ['school_id' => 9],
            metadata: []
        );
    }

    public function testCommitTransactionThrowsWhenPeriodLocked(): void
    {
        $this->db->table('ledger_period_locks')->insert([
            'school_id'    => 3,
            'period_start' => '2024-03-01',
            'period_end'   => '2024-03-31',
            'locked_at'    => '2024-04-01 00:00:00',
            'locked_by'    => 'auditor-1',
        ]);

        $ledger = new LedgerService($this->db, new AuditService($this->db));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Accounting period is locked.');

        $ledger->commitTransaction(
            transactionKey: 'txn-locked',
            entries: [
                ['account_code' => '1000', 'direction' => 'debit', 'amount' => '25.00'],
                ['account_code' => '2000', 'direction' => 'credit', 'amount' => '25.00'],
            ],
            context: ['school_id' => 3, 'transacted_at' => '2024-03-15 12:00:00'],
            metadata: []
        );
    }

    public function testScheduleReversalCreatesOppositeEntries(): void
    {
        $audit = new AuditService($this->db);
        $ledger = new LedgerService($this->db, $audit);

        $originalId = $ledger->commitTransaction(
            transactionKey: 'txn-2001',
            entries: [
                ['account_code' => '1100', 'direction' => 'debit', 'amount' => '75.00'],
                ['account_code' => '3100', 'direction' => 'credit', 'amount' => '75.00'],
            ],
            context: ['school_id' => 8],
            metadata: []
        );

        $reversalId = $ledger->scheduleReversal($originalId, ['school_id' => 8], 'Customer refund');

        $this->assertNotSame($originalId, $reversalId);

        $reversalEntries = $this->db->table('ledger_entries')->where('transaction_id', $reversalId)->orderBy('id')->get()->getResultArray();
        $this->assertCount(2, $reversalEntries);
        $this->assertSame('credit', $reversalEntries[0]['direction']);
        $this->assertSame('debit', $reversalEntries[1]['direction']);

        $reversalAudit = $this->db->table('audit_events')
            ->where('event_type', 'ledger_transaction_committed')
            ->like('event_key', 'txn-2001:reversal:')
            ->get()
            ->getFirstRow('array');

        $this->assertNotNull($reversalAudit);
    }
}
