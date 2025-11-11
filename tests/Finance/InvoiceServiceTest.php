<?php

declare(strict_types=1);

namespace Tests\Ci4\Finance;

use Modules\Finance\Domain\Invoice;
use Modules\Finance\Services\InvoiceRepositoryInterface;
use Modules\Finance\Services\InvoiceService;
use Modules\Foundation\Services\AuditService;
use Modules\Foundation\Services\LedgerService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class InvoiceServiceTest extends TestCase
{
    private InvoiceRepositoryInterface&MockObject $repository;
    private LedgerService&MockObject $ledgerService;
    private AuditService&MockObject $auditService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository   = $this->createMock(InvoiceRepositoryInterface::class);
        $this->ledgerService = $this->createMock(LedgerService::class);
        $this->auditService  = $this->createMock(AuditService::class);
    }

    public function testIssueInvoiceCommitsLedgerAndRecordsAudit(): void
    {
        $service = new InvoiceService($this->repository, $this->ledgerService, $this->auditService);

        $this->ledgerService
            ->expects($this->once())
            ->method('commitTransaction')
            ->with(
                $this->stringStartsWith('finance.invoice.INV-001.issue'),
                $this->callback(function (array $entries): bool {
                    $this->assertCount(2, $entries);
                    $this->assertSame('debit', $entries[0]['direction']);
                    $this->assertSame('1100-ACCOUNTS-RECEIVABLE', $entries[0]['account_code']);
                    $this->assertSame('1500.00', $entries[0]['amount']);

                    $this->assertSame('credit', $entries[1]['direction']);
                    $this->assertSame('4000-TUITION', $entries[1]['account_code']);
                    $this->assertSame('1500.00', $entries[1]['amount']);

                    return true;
                }),
                $this->arrayHasKey('currency'),
                $this->arrayHasKey('line_items')
            )
            ->willReturn(77);

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Invoice::class))
            ->willReturnCallback(static fn (Invoice $invoice): Invoice => $invoice);

        $this->auditService
            ->expects($this->once())
            ->method('recordEvent')
            ->with(
                'finance.invoice.INV-001',
                'invoice_issued',
                $this->arrayHasKey('tenant_id'),
                null,
                $this->callback(function (array $after): bool {
                    $this->assertSame('INV-001', $after['invoiceNumber']);
                    $this->assertSame('1500.00', $after['totalAmount']);

                    return true;
                }),
                $this->arrayHasKey('ledger_transaction_id')
            );

        $payload = [
            'invoice_number'     => 'INV-001',
            'student_id'         => 'student-99',
            'currency'           => 'KES',
            'receivable_account' => '1100-ACCOUNTS-RECEIVABLE',
            'items'              => [
                ['description' => 'Term 1 tuition', 'amount' => '1000', 'revenue_account' => '4000-TUITION'],
                ['description' => 'Activity fee', 'amount' => '500', 'revenue_account' => '4000-TUITION'],
            ],
        ];

        $context = [
            'tenant_id' => 'tenant-1',
            'actor_id'  => 'user-5',
        ];

        $invoice = $service->issueInvoice($payload, $context);

        $this->assertSame('issued', $invoice->getStatus());
        $this->assertSame(77, $invoice->getLedgerTransactionId());
        $this->assertSame('1500.00', $invoice->getTotalAmount());
    }

    public function testSettleInvoiceCreatesLedgerEntriesAndUpdatesStatus(): void
    {
        $service = new InvoiceService($this->repository, $this->ledgerService, $this->auditService);

        $invoice = new Invoice(
            'INV-002',
            'student-50',
            'KES',
            [
                ['description' => 'Tuition', 'amount' => '1500.00', 'revenue_account' => '4000-TUITION'],
            ],
            '1500.00',
            '1100-ACCOUNTS-RECEIVABLE'
        );
        $invoice->setLedgerTransactionId(44);

        $this->repository
            ->method('findByNumber')
            ->with('INV-002')
            ->willReturn($invoice);

        $this->ledgerService
            ->expects($this->once())
            ->method('commitTransaction')
            ->with(
                $this->stringStartsWith('finance.invoice.INV-002.settle'),
                $this->callback(function (array $entries): bool {
                    $this->assertCount(2, $entries);
                    $this->assertSame('debit', $entries[0]['direction']);
                    $this->assertSame('1200-WALLET', $entries[0]['account_code']);
                    $this->assertSame('1500.00', $entries[0]['amount']);
                    $this->assertSame('credit', $entries[1]['direction']);
                    $this->assertSame('1100-ACCOUNTS-RECEIVABLE', $entries[1]['account_code']);

                    return true;
                }),
                $this->arrayHasKey('currency'),
                $this->arrayHasKey('payment_method')
            )
            ->willReturn(88);

        $this->repository
            ->expects($this->once())
            ->method('markSettled')
            ->with($this->callback(function (Invoice $candidate): bool {
                $this->assertTrue($candidate->isSettled());
                $this->assertSame('wallet', $candidate->getPaymentMethod());
                $this->assertSame(88, $candidate->getSettlementTransactionId());

                return true;
            }))
            ->willReturnCallback(static fn (Invoice $invoice): Invoice => $invoice);

        $this->auditService
            ->expects($this->once())
            ->method('recordEvent')
            ->with(
                'finance.invoice.INV-002',
                'invoice_settled',
                $this->arrayHasKey('actor_id'),
                $this->isType('array'),
                $this->isType('array'),
                $this->arrayHasKey('ledger_transaction_id')
            );

        $payment = [
            'amount'       => '1500.00',
            'method'       => 'wallet',
            'account_code' => '1200-WALLET',
            'reference'    => 'TXN-7788',
        ];

        $context = [
            'tenant_id' => 'tenant-1',
            'actor_id'  => 'cashier-2',
        ];

        $result = $service->settleInvoice('INV-002', $payment, $context);

        $this->assertSame('settled', $result->getStatus());
        $this->assertSame('wallet', $result->getPaymentMethod());
        $this->assertSame(88, $result->getSettlementTransactionId());
    }
}
