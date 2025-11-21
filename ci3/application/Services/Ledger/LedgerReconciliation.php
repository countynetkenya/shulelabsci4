<?php

namespace App\Services\Ledger;

class LedgerReconciliation
{
    /**
     * @param iterable<array{amount?: float|int|string|null, type?: string|null}> $entries
     * @return array{
     *     opening_balance: float,
     *     ending_balance: float,
     *     total_credits: float,
     *     total_debits: float,
     *     net_change: float,
     *     entries: int,
     *     discrepancies: list<array{index: int|string, reason: string}>
     * }
     */
    public function reconcile(iterable $entries, float $openingBalance = 0.0): array
    {
        $balance = $openingBalance;
        $credits = 0.0;
        $debits = 0.0;
        $discrepancies = [];

        foreach ($entries as $index => $entry) {
            $amount = isset($entry['amount']) ? (float) $entry['amount'] : 0.0;
            $type = strtolower($entry['type'] ?? '');

            if ($amount < 0) {
                $discrepancies[] = ['index' => $index, 'reason' => 'Negative amount detected'];
            }

            if ($type === 'credit') {
                $credits += $amount;
                $balance += $amount;
            } elseif ($type === 'debit') {
                $debits += $amount;
                $balance -= $amount;
            } else {
                $discrepancies[] = ['index' => $index, 'reason' => 'Unknown entry type'];
            }
        }

        return [
            'opening_balance' => round($openingBalance, 2),
            'ending_balance' => round($balance, 2),
            'total_credits' => round($credits, 2),
            'total_debits' => round($debits, 2),
            'net_change' => round(($credits - $debits), 2),
            'entries' => count($entries),
            'discrepancies' => $discrepancies,
        ];
    }
}
