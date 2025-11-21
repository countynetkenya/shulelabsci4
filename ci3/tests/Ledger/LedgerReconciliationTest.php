<?php

declare(strict_types=1);

use App\Services\Ledger\LedgerReconciliation;
use PHPUnit\Framework\TestCase;

final class LedgerReconciliationTest extends TestCase
{
    public function testReconcileCalculatesNetBalance(): void
    {
        $service = new LedgerReconciliation();
        $entries = [
            ['type' => 'credit', 'amount' => 12000],
            ['type' => 'debit', 'amount' => 3500],
            ['type' => 'credit', 'amount' => 800],
            ['type' => 'debit', 'amount' => 200.50],
        ];

        $summary = $service->reconcile($entries, 5000.00);

        $this->assertSame(5000.0, $summary['opening_balance']);
        $this->assertSame(14099.5, $summary['ending_balance']);
        $this->assertSame(12800.0, $summary['total_credits']);
        $this->assertSame(3700.5, $summary['total_debits']);
        $this->assertEmpty($summary['discrepancies']);
    }

    public function testReconcileFlagsInvalidEntries(): void
    {
        $service = new LedgerReconciliation();
        $entries = [
            ['type' => 'credit', 'amount' => -100],
            ['type' => 'transfer', 'amount' => 450],
        ];

        $summary = $service->reconcile($entries);

        $this->assertCount(2, $summary['discrepancies']);
        $this->assertSame('Negative amount detected', $summary['discrepancies'][0]['reason']);
        $this->assertSame('Unknown entry type', $summary['discrepancies'][1]['reason']);
    }
}
