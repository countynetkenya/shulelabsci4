<?php

namespace Modules\Wallets\Controllers\Web;

use App\Controllers\BaseController;
use Modules\Wallets\Services\WalletService;

class WalletsController extends BaseController
{
    protected $walletService;

    public function __construct()
    {
        $this->walletService = new WalletService();
    }

    public function index()
    {
        $schoolId = session()->get('school_id');
        if (!$schoolId) {
            $schoolId = 1;
        }

        $data['wallets'] = $this->walletService->getWallets($schoolId);
        
        return view('Modules\Wallets\Views\wallets\index', $data);
    }

    public function topup($id)
    {
        // In a real app, we would fetch the wallet details here to show who we are topping up
        // For now, we just pass the ID
        $data['wallet_id'] = $id;
        return view('Modules\Wallets\Views\wallets\topup', $data);
    }

    public function processTopup($id)
    {
        $rules = [
            'amount' => 'required|numeric|greater_than[0]',
            'description' => 'required',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $amount = $this->request->getPost('amount');
        $description = $this->request->getPost('description');

        try {
            $this->walletService->credit($id, $amount, 'topup', $description);
            return redirect()->to('wallets')->with('message', 'Wallet topped up successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
