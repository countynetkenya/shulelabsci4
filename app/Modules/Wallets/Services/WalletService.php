<?php

namespace Modules\Wallets\Services;

use CodeIgniter\Database\ConnectionInterface;
use Config\Database;
use RuntimeException;

/**
 * WalletService - Handles wallet operations and transactions.
 */
class WalletService
{
    private $db;

    public function __construct(?ConnectionInterface $connection = null)
    {
        $this->db = $connection ?? Database::connect();
    }

    /**
     * Get all wallets for a school.
     *
     * @param int $schoolId
     * @param array $filters Optional filters
     * @return array
     */
    public function getWallets(int $schoolId, array $filters = []): array
    {
        $builder = $this->db->table('wallets')
            ->where('school_id', $schoolId);

        // Apply filters
        if (!empty($filters['search'])) {
            $builder->like('user_id', $filters['search']);
        }

        if (!empty($filters['wallet_type'])) {
            $builder->where('wallet_type', $filters['wallet_type']);
        }

        if (!empty($filters['status'])) {
            $builder->where('status', $filters['status']);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Get wallet by ID (scoped to school).
     *
     * @param int $id
     * @param int $schoolId
     * @return array|null
     */
    public function getWalletById(int $id, int $schoolId): ?array
    {
        $wallet = $this->db->table('wallets')
            ->where('id', $id)
            ->where('school_id', $schoolId)
            ->get()
            ->getRowArray();

        return $wallet ?: null;
    }

    /**
     * Get wallet summary for a school.
     *
     * @param int $schoolId
     * @return array
     */
    public function getSummary(int $schoolId): array
    {
        $result = $this->db->table('wallets')
            ->select('
                COUNT(*) as total_wallets,
                SUM(balance) as total_balance,
                AVG(balance) as average_balance,
                SUM(CASE WHEN status = "active" THEN 1 ELSE 0 END) as active_count,
                SUM(CASE WHEN status = "suspended" THEN 1 ELSE 0 END) as suspended_count
            ')
            ->where('school_id', $schoolId)
            ->get()
            ->getRowArray();

        return $result ?: [
            'total_wallets'    => 0,
            'total_balance'    => 0,
            'average_balance'  => 0,
            'active_count'     => 0,
            'suspended_count'  => 0,
        ];
    }

    /**
     * Update wallet.
     *
     * @param int $id
     * @param array $data
     * @param int $schoolId
     * @return bool
     */
    public function updateWallet(int $id, array $data, int $schoolId): bool
    {
        return $this->db->table('wallets')
            ->where('id', $id)
            ->where('school_id', $schoolId)
            ->update($data);
    }

    /**
     * Delete wallet.
     *
     * @param int $id
     * @param int $schoolId
     * @return bool
     */
    public function deleteWallet(int $id, int $schoolId): bool
    {
        return $this->db->table('wallets')
            ->where('id', $id)
            ->where('school_id', $schoolId)
            ->delete();
    }

    /**
     * Create a wallet for a user (enhanced version).
     */
    public function createWallet(array $data): int
    {
        $schoolId = $data['school_id'] ?? session('school_id');
        $userId = $data['user_id'];
        $walletType = $data['wallet_type'];

        $existing = $this->db->table('wallets')
            ->where('school_id', $schoolId)
            ->where('user_id', $userId)
            ->get()
            ->getRowArray();

        if ($existing) {
            return (int) $existing['id'];
        }

        $this->db->table('wallets')->insert([
            'school_id'   => $schoolId,
            'user_id'     => $userId,
            'wallet_type' => $walletType,
            'balance'     => $data['balance'] ?? 0,
            'currency'    => $data['currency'] ?? 'KES',
            'status'      => $data['status'] ?? 'active',
        ]);

        return (int) $this->db->insertID();
    }

    /**
     * Get wallet by user.
     */
    public function getWallet(int $userId, ?int $schoolId = null): ?array
    {
        $schoolId = $schoolId ?? session('school_id');

        return $this->db->table('wallets')
            ->where('school_id', $schoolId)
            ->where('user_id', $userId)
            ->get()
            ->getRowArray();
    }

    /**
     * Credit wallet (add funds).
     */
    public function credit(int $walletId, float $amount, string $category, string $description, ?string $referenceType = null, ?int $referenceId = null): int
    {
        return $this->executeTransaction($walletId, 'credit', $amount, $category, $description, $referenceType, $referenceId);
    }

    /**
     * Debit wallet (deduct funds).
     */
    public function debit(int $walletId, float $amount, string $category, string $description, ?string $referenceType = null, ?int $referenceId = null): int
    {
        // Check balance
        $wallet = $this->db->table('wallets')
            ->where('id', $walletId)
            ->get()
            ->getRowArray();

        if (!$wallet || $wallet['balance'] < $amount) {
            throw new RuntimeException('Insufficient wallet balance');
        }

        // Check spending limits
        $this->checkSpendingLimits($walletId, $amount);

        return $this->executeTransaction($walletId, 'debit', $amount, $category, $description, $referenceType, $referenceId);
    }

    /**
     * Transfer between wallets.
     */
    public function transfer(int $fromWalletId, int $toWalletId, float $amount, ?string $description = null): int
    {
        $this->db->transStart();

        // Create transfer record
        $this->db->table('wallet_transfers')->insert([
            'from_wallet_id' => $fromWalletId,
            'to_wallet_id' => $toWalletId,
            'amount' => $amount,
            'description' => $description,
            'status' => 'pending',
            'initiated_by' => session('user_id'),
        ]);
        $transferId = (int) $this->db->insertID();

        try {
            // Debit from source
            $this->debit($fromWalletId, $amount, 'transfer_out', "Transfer to wallet #{$toWalletId}", 'transfer', $transferId);

            // Credit to destination
            $this->credit($toWalletId, $amount, 'transfer_in', "Transfer from wallet #{$fromWalletId}", 'transfer', $transferId);

            // Mark transfer complete
            $this->db->table('wallet_transfers')
                ->where('id', $transferId)
                ->update([
                    'status' => 'completed',
                    'completed_at' => date('Y-m-d H:i:s'),
                ]);

            $this->db->transComplete();
            return $transferId;
        } catch (\Exception $e) {
            $this->db->table('wallet_transfers')
                ->where('id', $transferId)
                ->update(['status' => 'failed']);

            $this->db->transComplete();
            throw $e;
        }
    }

    /**
     * Top up wallet.
     */
    public function topUp(int $walletId, float $amount, string $paymentMethod, ?string $paymentReference = null): int
    {
        $this->db->table('wallet_topups')->insert([
            'wallet_id' => $walletId,
            'amount' => $amount,
            'payment_method' => $paymentMethod,
            'payment_reference' => $paymentReference,
            'status' => 'pending',
        ]);

        return (int) $this->db->insertID();
    }

    /**
     * Confirm top up and credit wallet.
     */
    public function confirmTopUp(int $topUpId): bool
    {
        $topUp = $this->db->table('wallet_topups')
            ->where('id', $topUpId)
            ->where('status', 'pending')
            ->get()
            ->getRowArray();

        if (!$topUp) {
            return false;
        }

        $this->db->transStart();

        $this->credit($topUp['wallet_id'], $topUp['amount'], 'topup', "Top-up via {$topUp['payment_method']}", 'topup', $topUpId);

        $this->db->table('wallet_topups')
            ->where('id', $topUpId)
            ->update([
                'status' => 'completed',
                'completed_at' => date('Y-m-d H:i:s'),
                'processed_by' => session('user_id'),
            ]);

        $this->db->transComplete();

        return $this->db->transStatus();
    }

    /**
     * Get transaction history.
     */
    public function getTransactions(int $walletId, int $limit = 50, int $offset = 0): array
    {
        return $this->db->table('wallet_transactions')
            ->where('wallet_id', $walletId)
            ->orderBy('created_at', 'DESC')
            ->limit($limit, $offset)
            ->get()
            ->getResultArray();
    }

    /**
     * Get wallet balance.
     */
    public function getBalance(int $walletId): float
    {
        $wallet = $this->db->table('wallets')
            ->select('balance')
            ->where('id', $walletId)
            ->get()
            ->getRowArray();

        return (float) ($wallet['balance'] ?? 0);
    }

    /**
     * Set spending limit.
     */
    public function setLimit(int $walletId, string $limitType, float $maxAmount): bool
    {
        $existing = $this->db->table('wallet_limits')
            ->where('wallet_id', $walletId)
            ->where('limit_type', $limitType)
            ->get()
            ->getRowArray();

        if ($existing) {
            return $this->db->table('wallet_limits')
                ->where('id', $existing['id'])
                ->update(['max_amount' => $maxAmount]);
        }

        return $this->db->table('wallet_limits')->insert([
            'wallet_id' => $walletId,
            'limit_type' => $limitType,
            'max_amount' => $maxAmount,
        ]);
    }

    /**
     * Execute a wallet transaction.
     */
    private function executeTransaction(int $walletId, string $type, float $amount, string $category, string $description, ?string $referenceType, ?int $referenceId): int
    {
        $this->db->transStart();

        // Get current balance with lock
        $wallet = $this->db->table('wallets')
            ->where('id', $walletId)
            ->get()
            ->getRowArray();

        $balanceBefore = (float) $wallet['balance'];
        $balanceAfter = $type === 'credit' ? $balanceBefore + $amount : $balanceBefore - $amount;

        // Update wallet balance
        $this->db->table('wallets')
            ->where('id', $walletId)
            ->update(['balance' => $balanceAfter]);

        // Create transaction record
        $transactionRef = $this->generateTransactionRef();
        $this->db->table('wallet_transactions')->insert([
            'wallet_id' => $walletId,
            'transaction_ref' => $transactionRef,
            'transaction_type' => $type,
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
            'category' => $category,
            'description' => $description,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'status' => 'completed',
            'created_by' => session('user_id'),
        ]);
        $transactionId = (int) $this->db->insertID();

        // Update spending limits if debit
        if ($type === 'debit') {
            $this->updateSpendingLimits($walletId, $amount);
        }

        $this->db->transComplete();

        return $transactionId;
    }

    /**
     * Check spending limits.
     */
    private function checkSpendingLimits(int $walletId, float $amount): void
    {
        $limits = $this->db->table('wallet_limits')
            ->where('wallet_id', $walletId)
            ->where('is_active', 1)
            ->get()
            ->getResultArray();

        foreach ($limits as $limit) {
            if ($limit['limit_type'] === 'per_transaction' && $amount > $limit['max_amount']) {
                throw new RuntimeException("Amount exceeds per-transaction limit of {$limit['max_amount']}");
            }

            $usage = (float) $limit['current_usage'];
            if ($usage + $amount > $limit['max_amount']) {
                throw new RuntimeException("Amount exceeds {$limit['limit_type']} spending limit");
            }
        }
    }

    /**
     * Update spending limits after debit.
     */
    private function updateSpendingLimits(int $walletId, float $amount): void
    {
        $this->db->table('wallet_limits')
            ->where('wallet_id', $walletId)
            ->where('is_active', 1)
            ->where('limit_type !=', 'per_transaction')
            ->set('current_usage', 'current_usage + ' . $amount, false)
            ->update();
    }

    /**
     * Generate unique transaction reference.
     */
    private function generateTransactionRef(): string
    {
        return 'TXN' . date('YmdHis') . strtoupper(bin2hex(random_bytes(4)));
    }
}
