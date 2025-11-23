<?php

namespace Modules\Finance\Services;

use InvalidArgumentException;
use Modules\Finance\Domain\Invoice;
use Modules\Foundation\Services\AuditService;
use Modules\Foundation\Services\LedgerService;
use RuntimeException;

// Polyfill for bcmath if not available
if (!function_exists('Modules\\Finance\\Services\\bcadd')) {
    function bcadd(string $num1, string $num2, ?int $scale = 0): string
    {
        return number_format((float) $num1 + (float) $num2, $scale, '.', '');
    }
}

if (!function_exists('Modules\\Finance\\Services\\bccomp')) {
    function bccomp(string $num1, string $num2, ?int $scale = 0): int
    {
        $result = round((float) $num1 - (float) $num2, $scale);
        if ($result > 0) {
            return 1;
        }
        if ($result < 0) {
            return -1;
        }
        return 0;
    }
}

/**
 * Coordinates invoice issuance and settlement with append-only ledger discipline.
 */
class InvoiceService
{
    private const DEFAULT_RECEIVABLE_ACCOUNT = '1100-ACCOUNTS-RECEIVABLE';
    private const DEFAULT_CASH_ACCOUNT = '1000-CASH-ON-HAND';

    public function __construct(
        private readonly InvoiceRepositoryInterface $repository,
        private readonly LedgerService $ledgerService,
        private readonly AuditService $auditService
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $context
     */
    public function issueInvoice(array $payload, array $context): Invoice
    {
        $invoiceNumber = trim((string) ($payload['invoice_number'] ?? ''));
        $studentId = trim((string) ($payload['student_id'] ?? ''));
        $currency = strtoupper((string) ($payload['currency'] ?? 'KES'));
        $receivableAccount = (string) ($payload['receivable_account'] ?? self::DEFAULT_RECEIVABLE_ACCOUNT);
        $itemsPayload = $payload['items'] ?? null;

        if ($invoiceNumber === '' || $studentId === '') {
            throw new InvalidArgumentException('Invoice number and student ID are required.');
        }

        if ($receivableAccount === '') {
            throw new InvalidArgumentException('Receivable account code cannot be empty.');
        }

        if (!is_array($itemsPayload) || $itemsPayload === []) {
            throw new InvalidArgumentException('At least one invoice line item is required.');
        }

        $lineItems = $this->normaliseLineItems($itemsPayload);
        $total = $this->calculateTotal($lineItems);

        $invoice = new Invoice(
            $invoiceNumber,
            $studentId,
            $currency,
            $lineItems,
            $total,
            $receivableAccount
        );

        $contextForLedger = $context;
        $contextForLedger['currency'] = $currency;

        $transactionId = $this->ledgerService->commitTransaction(
            transactionKey: sprintf('finance.invoice.%s.issue', $invoiceNumber),
            entries: $this->buildIssuanceEntries($receivableAccount, $lineItems, $invoiceNumber, $studentId),
            context: $contextForLedger,
            metadata: [
                'invoice_number' => $invoiceNumber,
                'student_id'     => $studentId,
                'line_items'     => $lineItems,
                'total'          => $total,
            ]
        );

        $invoice->setLedgerTransactionId($transactionId);
        $stored = $this->repository->save($invoice);

        $this->auditService->recordEvent(
            eventKey: sprintf('finance.invoice.%s', $invoiceNumber),
            eventType: 'invoice_issued',
            context: $context,
            before: null,
            after: $stored->toArray(),
            metadata: [
                'ledger_transaction_id' => $transactionId,
                'total'                 => $total,
                'currency'              => $currency,
            ]
        );

        return $stored;
    }

    /**
     * @param array<string, mixed> $payment
     * @param array<string, mixed> $context
     */
    public function settleInvoice(string $invoiceNumber, array $payment, array $context): Invoice
    {
        $invoice = $this->repository->findByNumber($invoiceNumber);

        if ($invoice === null) {
            throw new RuntimeException('Invoice not found.');
        }

        if ($invoice->isSettled()) {
            throw new RuntimeException('Invoice has already been settled.');
        }

        $paymentAmount = $this->normaliseMoney($payment['amount'] ?? null);
        $method = trim((string) ($payment['method'] ?? ''));
        $paymentAccount = (string) ($payment['account_code'] ?? self::DEFAULT_CASH_ACCOUNT);
        $reference = isset($payment['reference']) ? (string) $payment['reference'] : null;

        if ($method === '') {
            throw new InvalidArgumentException('Payment method is required.');
        }

        if ($paymentAccount === '') {
            throw new InvalidArgumentException('Payment account code cannot be empty.');
        }

        if (bccomp($invoice->getTotalAmount(), $paymentAmount, 2) !== 0) {
            throw new InvalidArgumentException('Payment amount must match invoice total.');
        }

        $before = $invoice->toArray();
        $contextForLedger = $context;
        $contextForLedger['currency'] = $invoice->getCurrencyCode();

        $settlementId = $this->ledgerService->commitTransaction(
            transactionKey: sprintf('finance.invoice.%s.settle', $invoiceNumber),
            entries: $this->buildSettlementEntries($invoice, $paymentAccount, $method),
            context: $contextForLedger,
            metadata: [
                'invoice_number' => $invoiceNumber,
                'payment_method' => $method,
                'reference'      => $reference,
            ]
        );

        $invoice->markSettled($settlementId, $method);
        $updated = $this->repository->markSettled($invoice);

        $metadata = [
            'ledger_transaction_id' => $settlementId,
            'payment_method'        => $method,
            'reference'             => $reference,
        ];

        $this->auditService->recordEvent(
            eventKey: sprintf('finance.invoice.%s', $invoiceNumber),
            eventType: 'invoice_settled',
            context: $context,
            before: $before,
            after: $updated->toArray(),
            metadata: $metadata
        );

        return $updated;
    }

    /**
     * @param array<int|string, mixed> $items
     * @return array<int, array{
     *     description: string,
     *     amount: string,
     *     revenue_account: string,
     *     metadata?: array<string, mixed>
     * }>
     */
    private function normaliseLineItems(array $items): array
    {
        $normalised = [];

        foreach ($items as $item) {
            if (!is_array($item)) {
                throw new InvalidArgumentException('Invoice line items must be arrays.');
            }

            $description = trim((string) ($item['description'] ?? ''));
            $amount = $item['amount'] ?? null;
            $revenueAccount = (string) ($item['revenue_account'] ?? '');

            if ($description === '') {
                throw new InvalidArgumentException('Each invoice item requires a description.');
            }

            if ($revenueAccount === '') {
                throw new InvalidArgumentException('Each invoice item requires a revenue account.');
            }

            $normalisedItem = [
                'description'     => $description,
                'amount'          => $this->normaliseMoney($amount),
                'revenue_account' => $revenueAccount,
            ];

            if (isset($item['metadata']) && is_array($item['metadata'])) {
                $normalisedItem['metadata'] = $item['metadata'];
            }

            $normalised[] = $normalisedItem;
        }

        return $normalised;
    }

    /**
     * @param list<array{description: string, amount: string, revenue_account: string, metadata?: array<string, mixed>}> $items
     */
    private function calculateTotal(array $items): string
    {
        $total = '0.00';

        foreach ($items as $item) {
            $total = bcadd($total, $item['amount'], 2);
        }

        return $total;
    }

    /**
     * @param list<array{description: string, amount: string, revenue_account: string, metadata?: array<string, mixed>}> $items
     * @return list<array{account_code: string, direction: string, amount: string, memo: string}>
     */
    private function buildIssuanceEntries(string $receivableAccount, array $items, string $invoiceNumber, string $studentId): array
    {
        $entries = [[
            'account_code' => $receivableAccount,
            'direction'    => 'debit',
            'amount'       => $this->calculateTotal($items),
            'memo'         => sprintf('Invoice %s issued for %s', $invoiceNumber, $studentId),
        ]];

        $revenueTotals = [];
        foreach ($items as $item) {
            $code = $item['revenue_account'];
            if (!isset($revenueTotals[$code])) {
                $revenueTotals[$code] = '0.00';
            }

            $revenueTotals[$code] = bcadd($revenueTotals[$code], $item['amount'], 2);
        }

        foreach ($revenueTotals as $account => $amount) {
            $entries[] = [
                'account_code' => $account,
                'direction'    => 'credit',
                'amount'       => $amount,
                'memo'         => sprintf('Invoice %s revenue allocation', $invoiceNumber),
            ];
        }

        return $entries;
    }

    /**
     * @return list<array{account_code: string, direction: string, amount: string, memo: string}>
     */
    private function buildSettlementEntries(Invoice $invoice, string $paymentAccount, string $method): array
    {
        $total = $invoice->getTotalAmount();

        return [
            [
                'account_code' => $paymentAccount,
                'direction'    => 'debit',
                'amount'       => $total,
                'memo'         => sprintf('Invoice %s settlement via %s', $invoice->getInvoiceNumber(), $method),
            ],
            [
                'account_code' => $invoice->getReceivableAccount(),
                'direction'    => 'credit',
                'amount'       => $total,
                'memo'         => sprintf('Invoice %s receivable cleared', $invoice->getInvoiceNumber()),
            ],
        ];
    }

    /**
     * @param mixed $amount
     */
    private function normaliseMoney($amount): string
    {
        if (!is_numeric($amount)) {
            throw new InvalidArgumentException('Monetary amounts must be numeric.');
        }

        if (is_string($amount)) {
            return bcadd($amount, '0', 2);
        }

        return number_format((float) $amount, 2, '.', '');
    }
}
