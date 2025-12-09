<?php

namespace Modules\Finance\Controllers;

use App\Controllers\BaseController;
use Modules\Finance\Services\InvoicesService;
use Modules\Finance\Services\PaymentsService;

class PaymentsController extends BaseController
{
    protected $paymentsService;

    protected $invoicesService;

    public function __construct()
    {
        $this->paymentsService = new PaymentsService();
        $this->invoicesService = new InvoicesService();
    }

    public function index()
    {
        $schoolId = session()->get('current_school_id') ?? 1;
        $payments = $this->paymentsService->getAllPayments($schoolId);

        return view('Modules\Finance\Views\finance\payments\index', ['payments' => $payments]);
    }

    public function create()
    {
        $invoiceId = $this->request->getGet('invoice_id');
        $invoice = null;

        if ($invoiceId) {
            $invoice = $this->invoicesService->getInvoiceById($invoiceId);
        }

        return view('Modules\Finance\Views\finance\payments\create', ['invoice' => $invoice]);
    }

    public function store()
    {
        $data = $this->request->getPost();
        $data['recorded_by'] = session()->get('user_id') ?? 1; // Default to 1 for dev

        if ($this->paymentsService->createPayment($data)) {
            return redirect()->to('/finance/invoices')->with('success', 'Payment recorded successfully.');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to record payment.');
    }
}
