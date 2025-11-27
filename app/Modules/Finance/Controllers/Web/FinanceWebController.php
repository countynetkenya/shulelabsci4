<?php

namespace Modules\Finance\Controllers\Web;

use App\Controllers\BaseController;
use Modules\Finance\Models\FinanceInvoiceModel;
use Modules\Finance\Models\FinancePaymentModel;

class FinanceWebController extends BaseController
{
    public function index()
    {
        $invoiceModel = new FinanceInvoiceModel();
        $paymentModel = new FinancePaymentModel();

        $data = [
            'total_invoiced' => $invoiceModel->selectSum('amount')->first()['amount'] ?? 0,
            'total_collected' => $paymentModel->selectSum('amount')->first()['amount'] ?? 0,
            'pending_invoices' => $invoiceModel->where('status', 'unpaid')->countAllResults(),
        ];

        return view('modules/finance/index', $data);
    }
}
