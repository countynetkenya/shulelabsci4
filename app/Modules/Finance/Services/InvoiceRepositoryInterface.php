<?php

namespace Modules\Finance\Services;

use Modules\Finance\Domain\Invoice;

interface InvoiceRepositoryInterface
{
    public function save(Invoice $invoice): Invoice;

    public function findByNumber(string $invoiceNumber): ?Invoice;

    public function markSettled(Invoice $invoice): Invoice;
}
