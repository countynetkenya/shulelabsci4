<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Studentstatementservice
{
    /** @var CI_Controller */
    protected $CI;

    /** @var array<int, array> */
    protected $termCache = [];

    /** @var array<int, array<int, object>> */
    protected $schoolYearCache = [];

    public function __construct()
    {
        $this->CI =& get_instance();
        $this->CI->load->model('studentrelation_m');
        $this->CI->load->model('invoice_m');
        $this->CI->load->model('creditmemo_m');
        $this->CI->load->model('payment_m');
        $this->CI->load->model('classes_m');
        $this->CI->load->model('section_m');
        $this->CI->load->model('studentgroup_m');
        $this->CI->load->model('schoolterm_m');
        $this->CI->load->model('globalpayment_m');
        $this->CI->load->model('usertype_m');
        $this->CI->load->model('schoolyear_m');
    }

    public function build(array $filters): array
    {
        $schoolID     = (int) ($filters['schoolID'] ?? 0);
        $schoolyearID = (int) ($filters['schoolyearID'] ?? 0);

        $students = $this->resolveStudents($filters);
        $classes  = pluck($this->CI->classes_m->get_order_by_classes(['schoolID' => $schoolID]), 'classes', 'classesID');
        $sections = pluck($this->CI->section_m->get_order_by_section(['schoolID' => $schoolID]), 'section', 'sectionID');
        $groups   = pluck($this->CI->studentgroup_m->get_order_by_studentgroup(['schoolID' => $schoolID]), 'studentgroup', 'studentgroupID');

        $results       = [];
        $totalRowCount = 0;

        $roleLookup = pluck(
            $this->CI->usertype_m->get_order_by_usertype_with_or(['schoolID' => $schoolID]),
            'usertype',
            'usertypeID'
        );

        foreach ($students as $student) {
            $statement = $this->buildStudentStatement($student, $filters);
            $statement['student']['class']   = $classes[$student->srclassesID] ?? '';
            $statement['student']['section'] = $sections[$student->srsectionID] ?? '';
            $statement['student']['group']   = $groups[$student->srstudentgroupID] ?? '';
            $statement['student']['role_id'] = (int) ($student->usertypeID ?? 0);
            $statement['student']['role']    = $roleLookup[$statement['student']['role_id']] ?? '';

            $results[]      = $statement;
            $totalRowCount += $statement['student']['row_count'];
        }

        return [
            'generated_at' => date(DATE_ATOM),
            'filters'      => $this->normaliseFilters($filters),
            'students'     => $results,
            'total_rows'   => $totalRowCount,
            'schoolyearID' => $schoolyearID,
        ];
    }

    protected function buildStudentStatement($student, array $filters): array
    {
        $schoolID     = (int) ($filters['schoolID'] ?? 0);
        $schoolyearID = (int) ($filters['schoolyearID'] ?? 0);
        $schooltermID = (int) ($filters['schooltermID'] ?? 0);

        $includeParentDetails = $this->normalizeBoolean($filters['includeParentDetails'] ?? null, true);
        $includeStudentDetails = $this->normalizeBoolean($filters['includeStudentDetails'] ?? null, true);

        $range = $this->resolveDateRange($filters, $schoolID, $schooltermID);
        $dateFrom = $range['from'];
        $dateTo   = $range['to'];

        $invoiceFilters = [
            'studentID' => $student->srstudentID,
            'deleted_at' => 1,
            'schoolID' => $schoolID,
        ];
        if ($schoolyearID > 0) {
            $invoiceFilters['schoolyearID'] = $schoolyearID;
        }
        if ($dateFrom) {
            $invoiceFilters['date >='] = $dateFrom;
        }
        if ($dateTo) {
            $invoiceFilters['date <='] = $dateTo;
        }

        $creditMemoFilters = $invoiceFilters;
        $paymentFilters    = [
            'studentID' => $student->srstudentID,
            'schoolID' => $schoolID,
        ];
        if ($schoolyearID > 0) {
            $paymentFilters['schoolyearID'] = $schoolyearID;
        }
        if ($dateFrom) {
            $paymentFilters['paymentdate >='] = $dateFrom;
        }
        if ($dateTo) {
            $paymentFilters['paymentdate <='] = $dateTo;
        }

        $bbfInvoiceFilters = [
            'studentID' => $student->srstudentID,
            'deleted_at' => 1,
            'schoolID' => $schoolID,
        ];
        $bbfCreditMemoFilters = $bbfInvoiceFilters;
        $bbfPaymentFilters    = [
            'studentID' => $student->srstudentID,
            'schoolID' => $schoolID,
        ];

        if ($schoolyearID > 0) {
            $bbfInvoiceFilters['schoolyearID <']     = $schoolyearID;
            $bbfCreditMemoFilters['schoolyearID <']  = $schoolyearID;
            $bbfPaymentFilters['schoolyearID <']     = $schoolyearID;
        } else {
            $bbfInvoiceFilters['schoolyearID']    = 0;
            $bbfCreditMemoFilters['schoolyearID'] = 0;
            $bbfPaymentFilters['schoolyearID']    = 0;
        }

        if ($dateFrom) {
            $bbfInvoiceFilters['date <']            = $dateFrom;
            $bbfCreditMemoFilters['date <']         = $dateFrom;
            $bbfPaymentFilters['paymentdate <']     = $dateFrom;
        }

        $invoices    = $this->CI->invoice_m->get_order_by_invoice($invoiceFilters);
        $creditmemos = $this->CI->creditmemo_m->get_order_by_creditmemo($creditMemoFilters);
        $payments    = $this->CI->payment_m->get_order_by_payment($paymentFilters);

        $bbfInvoices    = $this->CI->invoice_m->get_order_by_invoice($bbfInvoiceFilters);
        $bbfCreditmemos = $this->CI->creditmemo_m->get_order_by_creditmemo($bbfCreditMemoFilters);
        $bbfPayments    = $this->CI->payment_m->get_order_by_payment($bbfPaymentFilters);

        $openingBalance = $this->computeOpeningBalance($bbfInvoices, $bbfCreditmemos, $bbfPayments);

        $transactions = [];
        foreach ($invoices as $invoice) {
            $transactions[] = [
                'type' => 'invoice',
                'date' => $invoice->date,
                'amount' => (float) $invoice->amount,
                'description' => $this->formatInvoiceDescription($invoice),
                'direction' => 'debit',
                'reference' => (string) $invoice->invoiceID,
                'schoolyearID' => isset($invoice->schoolyearID) ? (int) $invoice->schoolyearID : 0,
            ];
        }

        foreach ($creditmemos as $creditmemo) {
            $transactions[] = [
                'type' => 'creditmemo',
                'date' => $creditmemo->date,
                'amount' => (float) $creditmemo->amount,
                'description' => $this->formatCreditMemoDescription($creditmemo),
                'direction' => 'credit',
                'reference' => (string) $creditmemo->creditmemoID,
                'schoolyearID' => isset($creditmemo->schoolyearID) ? (int) $creditmemo->schoolyearID : 0,
            ];
        }

        $globalPaymentLookup = [];
        $globalPayments = $this->CI->globalpayment_m->get_order_by_globalpayment([
            'studentID' => $student->srstudentID,
            'schoolID' => $schoolID,
        ]);
        foreach ($globalPayments as $globalPayment) {
            $globalPaymentLookup[$globalPayment->globalpaymentID] = $globalPayment;
        }

        foreach ($payments as $payment) {
            $transactions[] = [
                'type' => 'payment',
                'date' => $payment->paymentdate,
                'amount' => (float) $payment->paymentamount,
                'description' => $this->formatPaymentDescription($payment, $globalPaymentLookup),
                'direction' => 'credit',
                'reference' => (string) $payment->paymentID,
                'schoolyearID' => isset($payment->schoolyearID) ? (int) $payment->schoolyearID : 0,
            ];
        }

        usort($transactions, static function ($a, $b) {
            $dateA = $a['date'] ?? '';
            $dateB = $b['date'] ?? '';
            if ($dateA === $dateB) {
                return strcmp($a['type'], $b['type']);
            }
            return strcmp($dateA, $dateB);
        });

        $rows = [];
        $runningBalance = $openingBalance;
        $totalDebit = 0.0;
        $totalCredit = 0.0;
        $currentTermKey = null;
        $currentTermLabel = null;
        $currentTermDebit = 0.0;
        $currentTermCredit = 0.0;
        $currentTermYearID = null;
        $currentTermYearLabel = null;
        $termSummaries = [];
        $yearSummaries = [];

        $rows[] = [
            'date' => null,
            'description' => lang('finance_statement_opening_balance'),
            'debit' => 0.0,
            'credit' => 0.0,
            'balance' => round($runningBalance, 2),
            'running_balance' => round($runningBalance, 2),
            'term' => null,
            'term_label' => lang('finance_statement_opening_balance'),
            'day' => null,
            'type' => 'opening_balance',
            'reference' => null,
            'schoolyear_id' => null,
            'schoolyear_label' => null,
        ];

        foreach ($transactions as $transaction) {
            $termTitle = $this->resolveTermLabel($transaction['date'], $schoolID, $schoolyearID);
            $termKey = $termTitle ?: '__unassigned__';
            $termLabel = $termTitle ?: lang('finance_statement_term_unassigned');

            $transactionYearID = isset($transaction['schoolyearID']) ? (int) $transaction['schoolyearID'] : 0;
            $transactionYearLabel = $transactionYearID > 0
                ? $this->resolveSchoolYearLabel($schoolID, $transactionYearID)
                : lang('finance_statement_schoolyear_unassigned');

            if ($currentTermKey !== null && $termKey !== $currentTermKey) {
                $termSummaryRow = $this->buildTermSummaryRow(
                    $currentTermLabel,
                    $currentTermDebit,
                    $currentTermCredit,
                    $runningBalance,
                    $currentTermYearID,
                    $currentTermYearLabel
                );
                $rows[] = $termSummaryRow;
                $termSummaries[] = $this->transformSummaryRow($termSummaryRow);
                $currentTermDebit = 0.0;
                $currentTermCredit = 0.0;
                $currentTermYearID = null;
                $currentTermYearLabel = null;
            }

            if ($currentTermKey === null || $termKey !== $currentTermKey) {
                $currentTermKey = $termKey;
                $currentTermLabel = $termLabel;
                $currentTermYearID = $transactionYearID ?: $currentTermYearID;
                $currentTermYearLabel = $transactionYearID > 0 ? $transactionYearLabel : $currentTermYearLabel;
            } elseif (!$currentTermYearID && $transactionYearID > 0) {
                $currentTermYearID = $transactionYearID;
                $currentTermYearLabel = $transactionYearLabel;
            }

            $amount = round($transaction['amount'], 2);
            $debitValue = 0.0;
            $creditValue = 0.0;
            if ($transaction['direction'] === 'debit') {
                $runningBalance += $amount;
                $totalDebit += $amount;
                $currentTermDebit += $amount;
                $debitValue = $amount;
            } else {
                $runningBalance -= $amount;
                $totalCredit += $amount;
                $currentTermCredit += $amount;
                $creditValue = $amount;
            }

            $day = null;
            if (!empty($transaction['date'])) {
                $timestamp = strtotime($transaction['date']);
                if ($timestamp) {
                    $day = date('Y-m-d', $timestamp);
                }
            }

            $rows[] = [
                'date' => $transaction['date'],
                'description' => $transaction['description'],
                'debit' => $debitValue,
                'credit' => $creditValue,
                'balance' => round($runningBalance, 2),
                'running_balance' => round($runningBalance, 2),
                'term' => $termTitle,
                'term_label' => $termLabel,
                'day' => $day,
                'type' => $transaction['type'],
                'reference' => $transaction['reference'],
                'schoolyear_id' => $transactionYearID > 0 ? $transactionYearID : null,
                'schoolyear_label' => $transactionYearID > 0 ? $transactionYearLabel : null,
            ];

            $yearKey = $transactionYearID > 0 ? $transactionYearID : '__unassigned__';
            if (!isset($yearSummaries[$yearKey])) {
                $yearSummaries[$yearKey] = [
                    'label' => $transactionYearLabel,
                    'debit' => 0.0,
                    'credit' => 0.0,
                    'balance' => round($runningBalance, 2),
                    'schoolyear_id' => $transactionYearID > 0 ? $transactionYearID : null,
                    'schoolyear_label' => $transactionYearID > 0 ? $transactionYearLabel : null,
                ];
            }

            $yearSummaries[$yearKey]['debit'] += $debitValue;
            $yearSummaries[$yearKey]['credit'] += $creditValue;
            $yearSummaries[$yearKey]['balance'] = round($runningBalance, 2);
        }

        if ($currentTermKey !== null) {
            $termSummaryRow = $this->buildTermSummaryRow(
                $currentTermLabel,
                $currentTermDebit,
                $currentTermCredit,
                $runningBalance,
                $currentTermYearID,
                $currentTermYearLabel
            );
            $rows[] = $termSummaryRow;
            $termSummaries[] = $this->transformSummaryRow($termSummaryRow);
        }

        foreach ($yearSummaries as &$yearSummary) {
            $yearSummary['debit'] = round($yearSummary['debit'], 2);
            $yearSummary['credit'] = round($yearSummary['credit'], 2);
            $yearSummary['balance'] = round($yearSummary['balance'], 2);
            $yearSummary['period_balance'] = round($yearSummary['debit'] - $yearSummary['credit'], 2);
        }
        unset($yearSummary);

        $balanceSummary = [
            'opening_balance' => round($openingBalance, 2),
            'closing_balance' => round($runningBalance, 2),
            'total_debit' => round($totalDebit, 2),
            'total_credit' => round($totalCredit, 2),
            'terms' => $termSummaries,
            'academic_years' => array_values($yearSummaries),
        ];

        $studentAddress = isset($student->address) ? trim((string) $student->address) : '';
        $studentPhone = isset($student->phone) ? trim((string) $student->phone) : '';
        $studentEmail = isset($student->email) ? trim((string) $student->email) : '';

        return [
            'student' => [
                'studentID' => $student->srstudentID,
                'student_name' => $student->srname,
                'admission_number' => $student->srregisterNO,
                'opening_balance' => round($openingBalance, 2),
                'closing_balance' => round($runningBalance, 2),
                'total_debit' => round($totalDebit, 2),
                'total_credit' => round($totalCredit, 2),
                'row_count' => count($rows),
                'address' => $includeStudentDetails ? $studentAddress : '',
                'phone' => $includeStudentDetails ? $studentPhone : '',
                'email' => $includeStudentDetails ? $studentEmail : '',
            ],
            'rows' => $rows,
            'terms' => $this->groupRowsByTerm($rows),
            'parent' => $this->buildParentDetails($student, $includeParentDetails),
            'range' => $range,
            'balance_summary' => $balanceSummary,
        ];
    }

    protected function buildTermSummaryRow(
        ?string $termLabel,
        float $termDebit,
        float $termCredit,
        float $runningBalance,
        ?int $schoolYearID = null,
        ?string $schoolYearLabel = null
    ): array
    {
        $label = $termLabel ?: lang('finance_statement_term_unassigned');

        return [
            'date' => null,
            'description' => lang('finance_statement_term_subtotal'),
            'debit' => round($termDebit, 2),
            'credit' => round($termCredit, 2),
            'balance' => round($runningBalance, 2),
            'running_balance' => round($runningBalance, 2),
            'period_balance' => round($termDebit - $termCredit, 2),
            'term' => $label,
            'term_label' => $label,
            'day' => null,
            'type' => 'term_summary',
            'reference' => null,
            'schoolyear_id' => $schoolYearID,
            'schoolyear_label' => $schoolYearLabel,
        ];
    }

    protected function transformSummaryRow(array $row): array
    {
        return [
            'label' => $row['term_label'] ?? '',
            'debit' => round((float) ($row['debit'] ?? 0.0), 2),
            'credit' => round((float) ($row['credit'] ?? 0.0), 2),
            'balance' => round((float) ($row['balance'] ?? 0.0), 2),
            'period_balance' => round((float) ($row['period_balance'] ?? (($row['debit'] ?? 0.0) - ($row['credit'] ?? 0.0))), 2),
            'schoolyear_id' => $row['schoolyear_id'] ?? null,
            'schoolyear_label' => $row['schoolyear_label'] ?? null,
        ];
    }

    protected function resolveStudents(array $filters)
    {
        $schoolID     = (int) ($filters['schoolID'] ?? 0);
        $schoolyearID = (int) ($filters['schoolyearID'] ?? 0);
        $classesID    = (int) ($filters['classesID'] ?? 0);
        $sectionID    = (int) ($filters['sectionID'] ?? 0);
        $studentID    = (int) ($filters['studentID'] ?? 0);
        $parentID     = (int) ($filters['parentID'] ?? 0);
        $usertypeID   = (int) ($filters['usertypeID'] ?? 0);
        $loginuserID  = (int) ($filters['loginuserID'] ?? 0);
        $roleID      = (int) ($filters['roleID'] ?? 0);

        if ($usertypeID === 3) {
            $studentID = $loginuserID;
            $parentID  = 0;
        } elseif ($usertypeID === 4) {
            $parentID = $loginuserID;
            $studentID = 0;
        }

        if ($studentID > 0) {
            $studentArray = [
                'srstudentID' => $studentID,
                'srschoolID' => $schoolID,
            ];
            if ($schoolyearID > 0) {
                $studentArray['srschoolyearID'] = $schoolyearID;
            }
            return $this->CI->studentrelation_m->general_get_order_by_student_with_parent($studentArray);
        }

        if ($parentID > 0) {
            $parentArray = [
                'parentID' => $parentID,
                'schoolID' => $schoolID,
            ];
            if ($schoolyearID > 0) {
                $parentArray['srschoolyearID'] = $schoolyearID;
            }
            return $this->CI->studentrelation_m->general_get_order_by_student_with_parent($parentArray);
        }

        $baseFilters = ['srschoolID' => $schoolID];
        if ($schoolyearID > 0) {
            $baseFilters['srschoolyearID'] = $schoolyearID;
        }
        if ($classesID > 0) {
            $baseFilters['srclassesID'] = $classesID;
        }
        if ($sectionID > 0) {
            $baseFilters['srsectionID'] = $sectionID;
        }
        if ($roleID > 0) {
            $baseFilters['usertypeID'] = $roleID;
        }

        return $this->CI->studentrelation_m->general_get_order_by_student_with_parent($baseFilters);
    }

    protected function resolveDateRange(array $filters, int $schoolID, int $schooltermID): array
    {
        $dateFrom = $this->sanitizeDate($filters['dateFrom'] ?? null);
        $dateTo   = $this->sanitizeDate($filters['dateTo'] ?? null);
        $month    = $filters['month'] ?? null;
        $day      = $this->sanitizeDate($filters['specificDate'] ?? null);

        if ($day) {
            $dateFrom = $day;
            $dateTo   = $day;
        } elseif ($month) {
            $timestamp = strtotime($month . '-01');
            if ($timestamp) {
                $dateFrom = date('Y-m-01', $timestamp);
                $dateTo   = date('Y-m-t', $timestamp);
            }
        }

        if ($schooltermID > 0) {
            $term = $this->CI->schoolterm_m->get_single_schoolterm([
                'schooltermID' => $schooltermID,
                'schoolID' => $schoolID,
            ]);
            if ($term) {
                $termStart = $term->startingdate;
                $termEnd   = $term->endingdate;

                if (!$dateFrom || $dateFrom < $termStart) {
                    $dateFrom = $termStart;
                }
                if (!$dateTo || $dateTo > $termEnd) {
                    $dateTo = $termEnd;
                }
            }
        }

        if ($dateFrom && $dateTo && $dateFrom > $dateTo) {
            $tmp = $dateFrom;
            $dateFrom = $dateTo;
            $dateTo = $tmp;
        }

        return [
            'from' => $dateFrom,
            'to' => $dateTo,
        ];
    }

    protected function sanitizeDate($value)
    {
        if (!$value) {
            return null;
        }
        $timestamp = strtotime($value);
        if (!$timestamp) {
            return null;
        }
        return date('Y-m-d', $timestamp);
    }

    protected function computeOpeningBalance($invoices, $creditmemos, $payments): float
    {
        $balance = 0.0;
        foreach ($invoices as $invoice) {
            $balance += (float) $invoice->amount;
        }
        foreach ($creditmemos as $creditmemo) {
            $balance -= (float) $creditmemo->amount;
        }
        foreach ($payments as $payment) {
            $balance -= (float) $payment->paymentamount;
        }
        return round($balance, 2);
    }

    protected function formatInvoiceDescription($invoice): string
    {
        $feeType = $invoice->feetype ?: ($invoice->bundlefeetype ?: $invoice->productsaleitem);
        return sprintf('Invoice #%s - %s', $invoice->invoiceID, $feeType);
    }

    protected function formatCreditMemoDescription($creditmemo): string
    {
        return sprintf('Credit Memo #%s - %s', $creditmemo->creditmemoID, $creditmemo->credittype);
    }

    protected function formatPaymentDescription($payment, array $globalPaymentLookup): string
    {
        $reference = $payment->globalpaymentID ? 'Payment Ref ' . $payment->globalpaymentID : 'Payment';
        $pieces = [$reference];
        if (!empty($payment->paymenttype)) {
            $pieces[] = $payment->paymenttype;
        }
        if (!empty($payment->transactionID)) {
            $pieces[] = $payment->transactionID;
        }
        if (!empty($globalPaymentLookup[$payment->globalpaymentID])) {
            $gateway = $globalPaymentLookup[$payment->globalpaymentID];
            if (isset($gateway->paymentID)) {
                $pieces[] = 'Gateway #' . $gateway->paymentID;
            }
        }
        return implode('; ', $pieces);
    }

    protected function resolveTermLabel(?string $date, int $schoolID, int $schoolyearID)
    {
        if (!$date) {
            return null;
        }
        $terms = $this->getTerms($schoolID, $schoolyearID);
        foreach ($terms as $term) {
            if ($term->startingdate <= $date && $term->endingdate >= $date) {
                return $term->schooltermtitle;
            }
        }
        return null;
    }

    protected function getTerms(int $schoolID, int $schoolyearID): array
    {
        if (!isset($this->termCache[$schoolID])) {
            $this->termCache[$schoolID] = [];
        }

        $cacheKey = $schoolyearID > 0 ? $schoolyearID : 0;

        if (!isset($this->termCache[$schoolID][$cacheKey])) {
            $filters = ['schoolID' => $schoolID];
            if ($schoolyearID > 0) {
                $filters['schoolyearID'] = $schoolyearID;
            }
            $this->termCache[$schoolID][$cacheKey] = $this->CI->schoolterm_m->get_order_by_schoolterm($filters);
        }

        return $this->termCache[$schoolID][$cacheKey];
    }

    protected function resolveSchoolYearLabel(int $schoolID, int $schoolyearID): string
    {
        if ($schoolyearID <= 0) {
            return lang('finance_statement_schoolyear_unassigned');
        }

        $years = $this->getSchoolYears($schoolID);
        if (isset($years[$schoolyearID]) && isset($years[$schoolyearID]->schoolyear)) {
            return (string) $years[$schoolyearID]->schoolyear;
        }

        return lang('finance_statement_schoolyear_unassigned');
    }

    protected function getSchoolYears(int $schoolID): array
    {
        if (!isset($this->schoolYearCache[$schoolID])) {
            $records = $this->CI->schoolyear_m->get_order_by_schoolyear(['schoolID' => $schoolID]);
            $lookup = [];
            foreach ($records as $record) {
                $lookup[(int) $record->schoolyearID] = $record;
            }
            $this->schoolYearCache[$schoolID] = $lookup;
        }

        return $this->schoolYearCache[$schoolID];
    }

    protected function normaliseFilters(array $filters): array
    {
        $keys = ['classesID', 'sectionID', 'studentID', 'parentID', 'roleID', 'schoolyearID', 'schooltermID', 'dateFrom', 'dateTo', 'month', 'specificDate', 'includeParentDetails', 'includeStudentDetails'];
        $output = [];
        foreach ($keys as $key) {
            if (array_key_exists($key, $filters)) {
                $output[$key] = $filters[$key];
            }
        }
        return $output;
    }

    protected function groupRowsByTerm(array $rows): array
    {
        $groups = [];
        foreach ($rows as $row) {
            $type = $row['type'] ?? '';
            $isOpening = $type === 'opening_balance';
            $isTermSummary = $type === 'term_summary';
            $termTitle = $row['term'] ?? null;
            $termLabel = $row['term_label'] ?? ($termTitle ?: lang('finance_statement_term_unassigned'));
            $label = $isOpening ? lang('finance_statement_opening_balance') : $termLabel;
            $key = $isOpening ? '__opening__' : ($termTitle ?: ($termLabel ?: '__unassigned__'));

            if (!isset($groups[$key])) {
                $groups[$key] = [
                    'label' => $label,
                    'rows' => [],
                    'subtotal' => [
                        'debit' => 0.0,
                        'credit' => 0.0,
                        'balance' => 0.0,
                    ],
                    'is_opening' => $isOpening,
                    'has_transactions' => false,
                ];
            }

            $groups[$key]['rows'][] = $row;

            if ($isOpening) {
                $groups[$key]['subtotal']['balance'] = (float) ($row['balance'] ?? 0.0);
                continue;
            }

            if ($isTermSummary) {
                $groups[$key]['subtotal']['debit'] = round((float) ($row['debit'] ?? 0.0), 2);
                $groups[$key]['subtotal']['credit'] = round((float) ($row['credit'] ?? 0.0), 2);
                $groups[$key]['subtotal']['balance'] = round((float) ($row['balance'] ?? 0.0), 2);
                continue;
            }

            $groups[$key]['subtotal']['debit'] += (float) ($row['debit'] ?? 0.0);
            $groups[$key]['subtotal']['credit'] += (float) ($row['credit'] ?? 0.0);
            $groups[$key]['subtotal']['balance'] = (float) ($row['balance'] ?? 0.0);
            $groups[$key]['has_transactions'] = true;
        }

        foreach ($groups as &$group) {
            $group['subtotal']['debit'] = round($group['subtotal']['debit'], 2);
            $group['subtotal']['credit'] = round($group['subtotal']['credit'], 2);
            $group['subtotal']['balance'] = round($group['subtotal']['balance'], 2);
        }
        
        return array_values($groups);
    }

    protected function normalizeBoolean($value, bool $default = true): bool
    {
        if ($value === null) {
            return $default;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value)) {
            return $value !== 0;
        }

        if (is_string($value)) {
            $filtered = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($filtered !== null) {
                return $filtered;
            }
            return $value !== '0' && $value !== '';
        }

        return $default;
    }

    protected function buildParentDetails($student, bool $include): array
    {
        if (!$include) {
            return [];
        }

        $parentName = $student->parent_name ?? '';
        $parentEmail = $student->parent_email ?? '';
        $parentPhone = $student->parent_phone ?? '';
        $parentAddress = $student->parent_address ?? '';

        if ($parentName === '' && $parentEmail === '' && $parentPhone === '' && $parentAddress === '') {
            return [];
        }

        return [
            'name' => $parentName,
            'email' => $parentEmail,
            'phone' => $parentPhone,
            'address' => $parentAddress,
        ];
    }
}
