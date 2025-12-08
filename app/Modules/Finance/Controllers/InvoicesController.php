<?php

namespace Modules\Finance\Controllers;

use App\Controllers\BaseController;
use Modules\Finance\Services\InvoicesService;

class InvoicesController extends BaseController
{
    protected $invoicesService;

    public function __construct()
    {
        $this->invoicesService = new InvoicesService();
    }

    public function index()
    {
        $schoolId = session()->get('current_school_id') ?? 1; // Default to 1 for dev
        $invoices = $this->invoicesService->getAllInvoices($schoolId);
        
        return view('Modules\Finance\Views\finance\invoices\index', ['invoices' => $invoices]);
    }

    public function create()
    {
        $schoolId = session()->get('current_school_id') ?? 1;
        $students = $this->invoicesService->getStudentsForSchool($schoolId);
        
        return view('Modules\Finance\Views\finance\invoices\create', ['students' => $students]);
    }

    public function store()
    {
        $data = $this->request->getPost();
        $data['school_id'] = session()->get('current_school_id') ?? 1;
        
        if ($this->invoicesService->createInvoice($data)) {
            return redirect()->to('/finance/invoices')->with('success', 'Invoice created successfully.');
        }
        
        return redirect()->back()->withInput()->with('error', 'Failed to create invoice.');
    }

    public function show($id)
    {
        $invoice = $this->invoicesService->getInvoiceById($id);
        if (!$invoice) {
            return redirect()->to('/finance/invoices')->with('error', 'Invoice not found.');
        }
        
        return view('Modules\Finance\Views\finance\invoices\show', ['invoice' => $invoice]);
    }
}
