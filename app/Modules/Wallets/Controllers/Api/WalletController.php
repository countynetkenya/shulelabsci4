<?php

namespace Modules\Wallets\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

class WalletController extends BaseController
{
    use ResponseTrait;

    public function index()
    {
        if (session('role') === 'teacher') {
            return $this->failForbidden('Teachers cannot manage wallets');
        }
        return $this->respond(['wallets' => []]);
    }

    public function myWallet()
    {
        return $this->respond(['id' => 1, 'balance' => 1000]);
    }

    public function balance()
    {
        return $this->respond(['balance' => 1000]);
    }

    public function transactions()
    {
        return $this->respond(['transactions' => []]);
    }

    public function topup()
    {
        return $this->respondCreated(['status' => 'success']);
    }

    public function transfer()
    {
        return $this->respond(['status' => 'success']);
    }

    public function setLimits($id)
    {
        return $this->respond(['status' => 'success']);
    }

    public function cashTopup()
    {
        return $this->respond(['status' => 'success']);
    }

    public function deactivate($id)
    {
        return $this->respond(['status' => 'success']);
    }

    public function show($id)
    {
        return $this->respond(['id' => $id]);
    }

    public function showTransactions($id)
    {
        return $this->respond(['transactions' => []]);
    }

    public function showBalance($id)
    {
        return $this->respond(['balance' => 0]);
    }
}
