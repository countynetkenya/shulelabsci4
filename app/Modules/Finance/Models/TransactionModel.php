<?php

namespace App\Modules\Finance\Models;

use CodeIgniter\Model;

/**
 * TransactionModel - Handles finance payment transactions.
 *
 * Manages all financial transactions including:
 * - Fee payments
 * - Invoice payments
 * - Transaction records
 *
 * All data is tenant-scoped by school_id.
 */
class TransactionModel extends Model
{
    protected $table = 'finance_payments';

    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType = 'array';

    protected $useSoftDeletes = true;

    protected $protectFields = true;

    protected $allowedFields = [
        'school_id',
        'invoice_id',
        'amount',
        'method',
        'reference_code',
        'paid_at',
        'recorded_by',
    ];

    // Dates
    protected $useTimestamps = true;

    protected $dateFormat = 'datetime';

    protected $createdField = 'created_at';

    protected $updatedField = 'updated_at';

    protected $deletedField = 'deleted_at';

    // Validation
    protected $validationRules = [
        'school_id'    => 'required|integer',
        'invoice_id'   => 'required|integer',
        'amount'       => 'required|decimal|greater_than[0]',
        'method'       => 'required|in_list[cash,bank_transfer,mobile_money,cheque]',
        'paid_at'      => 'required|valid_date',
        'recorded_by'  => 'required|integer',
    ];

    protected $validationMessages = [
        'school_id' => [
            'required' => 'School is required',
            'integer'  => 'Invalid school ID',
        ],
        'invoice_id' => [
            'required' => 'Invoice is required',
            'integer'  => 'Invalid invoice ID',
        ],
        'amount' => [
            'required'      => 'Amount is required',
            'decimal'       => 'Amount must be a valid decimal',
            'greater_than'  => 'Amount must be greater than 0',
        ],
        'method' => [
            'required' => 'Payment method is required',
            'in_list'  => 'Invalid payment method',
        ],
    ];

    protected $skipValidation = false;

    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;

    protected $beforeInsert = [];

    protected $afterInsert = [];

    protected $beforeUpdate = [];

    protected $afterUpdate = [];

    protected $beforeFind = [];

    protected $afterFind = [];

    protected $beforeDelete = [];

    protected $afterDelete = [];

    /**
     * Get all transactions for a school.
     *
     * @param int $schoolId
     * @param array $filters Optional filters (search, method, date_from, date_to)
     * @return array
     */
    public function getTransactionsBySchool(int $schoolId, array $filters = []): array
    {
        $builder = $this->where('school_id', $schoolId);

        // Apply filters
        if (!empty($filters['search'])) {
            $builder->groupStart()
                ->like('reference_code', $filters['search'])
                ->orLike('method', $filters['search'])
                ->groupEnd();
        }

        if (!empty($filters['method'])) {
            $builder->where('method', $filters['method']);
        }

        if (!empty($filters['date_from'])) {
            $builder->where('paid_at >=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $builder->where('paid_at <=', $filters['date_to']);
        }

        return $builder->orderBy('paid_at', 'DESC')
            ->orderBy('created_at', 'DESC')
            ->findAll();
    }

    /**
     * Get payment methods used in the school.
     *
     * @param int $schoolId
     * @return array Array of unique payment methods
     */
    public function getPaymentMethods(int $schoolId): array
    {
        $results = $this->select('DISTINCT method as payment_method', false)
            ->where('school_id', $schoolId)
            ->whereNotNull('method')
            ->findAll();

        return array_column($results, 'payment_method');
    }

    /**
     * Get transaction summary for a school.
     *
     * @param int $schoolId
     * @param string|null $startDate
     * @param string|null $endDate
     * @return array
     */
    public function getTransactionSummary(int $schoolId, ?string $startDate = null, ?string $endDate = null): array
    {
        $builder = $this->select('
                COUNT(*) as total_transactions,
                SUM(amount) as total_amount,
                AVG(amount) as average_amount,
                MIN(amount) as min_amount,
                MAX(amount) as max_amount
            ')
            ->where('school_id', $schoolId);

        if ($startDate) {
            $builder->where('paid_at >=', $startDate);
        }

        if ($endDate) {
            $builder->where('paid_at <=', $endDate);
        }

        $result = $builder->get()->getRowArray();

        return $result ?: [
            'total_transactions' => 0,
            'total_amount'       => 0,
            'average_amount'     => 0,
            'min_amount'         => 0,
            'max_amount'         => 0,
        ];
    }

    /**
     * Get transactions by invoice.
     *
     * @param int $invoiceId
     * @param int $schoolId
     * @return array
     */
    public function getTransactionsByInvoice(int $invoiceId, int $schoolId): array
    {
        return $this->where('invoice_id', $invoiceId)
            ->where('school_id', $schoolId)
            ->orderBy('paid_at', 'DESC')
            ->findAll();
    }

    /**
     * Get total amount paid for an invoice.
     *
     * @param int $invoiceId
     * @param int $schoolId
     * @return float
     */
    public function getTotalPaidForInvoice(int $invoiceId, int $schoolId): float
    {
        $result = $this->selectSum('amount')
            ->where('invoice_id', $invoiceId)
            ->where('school_id', $schoolId)
            ->get()
            ->getRowArray();

        return (float) ($result['amount'] ?? 0);
    }
}
