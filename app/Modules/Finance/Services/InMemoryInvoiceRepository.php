<?php

namespace Modules\Finance\Services;

use Modules\Finance\Domain\Invoice;

class InMemoryInvoiceRepository implements InvoiceRepositoryInterface
{
    /**
     * @var array<string, Invoice>
     */
    private array $store = [];

    public function save(Invoice $invoice): Invoice
    {
        $this->store[$invoice->getInvoiceNumber()] = $invoice;

        return $invoice;
    }

    public function findByNumber(string $invoiceNumber): ?Invoice
    {
        return $this->store[$invoiceNumber] ?? null;
    }

    public function markSettled(Invoice $invoice): Invoice
    {
        $this->store[$invoice->getInvoiceNumber()] = $invoice;

        return $invoice;
    }
}
