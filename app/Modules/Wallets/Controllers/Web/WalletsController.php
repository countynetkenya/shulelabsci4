<?php

namespace Modules\Wallets\Controllers\Web;

use App\Controllers\BaseController;
use Modules\Wallets\Services\WalletService;

/**
 * WalletsController - Handles CRUD operations for wallets
 * 
 * All data is tenant-scoped by school_id from session.
 */
class WalletsController extends BaseController
{
    protected WalletService $service;

    public function __construct()
    {
        $this->service = new WalletService();
    }

    /**
     * Check if user has permission to access wallets module
     */
    protected function checkAccess(): bool
    {
        $usertypeID = session()->get('usertypeID');
        $isAdmin = in_array($usertypeID, [0, 1, '0', '1']);
        
        if ($isAdmin) {
            return true;
        }

        try {
            $auth = service('auth');
            if ($auth && method_exists($auth, 'can')) {
                return $auth->can('wallets.view');
            }
        } catch (\Throwable $e) {
            // Auth service not available
        }

        return $isAdmin;
    }

    /**
     * Get current school ID from session
     */
    protected function getSchoolId(): int
    {
        return (int) (session()->get('school_id') ?? session()->get('schoolID') ?? 1);
    }

    /**
     * List all wallets
     */
    public function index()
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied. Please log in.');
        }

        $schoolId = $this->getSchoolId();
        
        $filters = [
            'search'      => $this->request->getGet('search'),
            'wallet_type' => $this->request->getGet('wallet_type'),
            'status'      => $this->request->getGet('status'),
        ];

        $data = [
            'wallets' => $this->service->getWallets($schoolId, array_filter($filters)),
            'summary' => $this->service->getSummary($schoolId),
            'filters' => $filters,
        ];

        return view('Modules\Wallets\Views\index', $data);
    }

    /**
     * Show create form
     */
    public function create()
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied. Please log in.');
        }

        return view('Modules\Wallets\Views\create');
    }

    /**
     * Store a new wallet
     */
    public function store()
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied. Please log in.');
        }

        $schoolId = $this->getSchoolId();
        
        $rules = [
            'user_id'     => 'required|integer',
            'wallet_type' => 'required|in_list[student,parent,staff]',
            'currency'    => 'permit_empty|max_length[3]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'school_id'   => $schoolId,
            'user_id'     => $this->request->getPost('user_id'),
            'wallet_type' => $this->request->getPost('wallet_type'),
            'balance'     => 0,
            'currency'    => $this->request->getPost('currency') ?: 'KES',
            'status'      => 'active',
        ];

        $result = $this->service->createWallet($data);

        if ($result) {
            return redirect()->to('/wallets')->with('message', 'Wallet created successfully!');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to create wallet. Please try again.');
    }

    /**
     * Show edit form
     */
    public function edit(int $id)
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied. Please log in.');
        }

        $schoolId = $this->getSchoolId();
        $wallet = $this->service->getWalletById($id, $schoolId);
        
        if (!$wallet) {
            return redirect()->to('/wallets')->with('error', 'Wallet not found.');
        }

        $data = ['wallet' => $wallet];

        return view('Modules\Wallets\Views\edit', $data);
    }

    /**
     * Update an existing wallet
     */
    public function update(int $id)
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied. Please log in.');
        }

        $schoolId = $this->getSchoolId();

        $existingWallet = $this->service->getWalletById($id, $schoolId);
        if (!$existingWallet) {
            return redirect()->to('/wallets')->with('error', 'Wallet not found.');
        }

        $rules = [
            'user_id'     => 'required|integer',
            'wallet_type' => 'required|in_list[student,parent,staff]',
            'status'      => 'required|in_list[active,suspended,closed]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'user_id'     => $this->request->getPost('user_id'),
            'wallet_type' => $this->request->getPost('wallet_type'),
            'status'      => $this->request->getPost('status'),
        ];

        $result = $this->service->updateWallet($id, $data, $schoolId);

        if ($result) {
            return redirect()->to('/wallets')->with('message', 'Wallet updated successfully!');
        }

        return redirect()->back()->withInput()->with('error', 'Failed to update wallet. Please try again.');
    }

    /**
     * Delete a wallet
     */
    public function delete(int $id)
    {
        if (!$this->checkAccess()) {
            return redirect()->to('/login')->with('error', 'Access denied. Please log in.');
        }

        $schoolId = $this->getSchoolId();
        
        $wallet = $this->service->getWalletById($id, $schoolId);
        if (!$wallet) {
            return redirect()->to('/wallets')->with('error', 'Wallet not found.');
        }

        $result = $this->service->deleteWallet($id, $schoolId);

        if ($result) {
            return redirect()->to('/wallets')->with('message', 'Wallet deleted successfully!');
        }

        return redirect()->to('/wallets')->with('error', 'Failed to delete wallet. Please try again.');
    }

    /**
     * Show topup form (legacy)
     */
    public function topup($id)
    {
        $data['wallet_id'] = $id;
        return view('Modules\Wallets\Views\wallets\topup', $data);
    }

    /**
     * Process topup (legacy)
     */
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
            $this->service->credit($id, $amount, 'topup', $description);
            return redirect()->to('wallets')->with('message', 'Wallet topped up successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
