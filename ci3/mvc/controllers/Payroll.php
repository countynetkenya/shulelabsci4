<?php

use App\Services\Payroll\PayrollCalculator;

defined('BASEPATH') or exit('No direct script access allowed');

class Payroll extends Admin_Controller
{
    /** @var PayrollCalculator */
    protected $calculator;

    /** @var array<string, object|null> */
    protected $userCache = [];

    /** @var array<int, object|null> */
    protected $salaryTemplateCache = [];

    /** @var array<int, array{allowances: array<int, array>, deductions: array<int, array>}> */
    protected $templateOptionsCache = [];

    /** @var array<int, object|null> */
    protected $hourlyTemplateCache = [];

    public function __construct()
    {
        parent::__construct();
        require_feature_flag('PAYROLL_V2');

        $language = $this->session->userdata('lang');
        $this->lang->load('menu', $language);
        $this->lang->load('payroll', $language);

        $pageTitle = lang('menu_payroll');
        if ($pageTitle === 'menu_payroll') {
            $pageTitle = 'Payroll';
        }

        $this->data['pageTitle'] = $pageTitle;

        $this->load->model('manage_salary_m');
        $this->load->model('salary_template_m');
        $this->load->model('salaryoption_m');
        $this->load->model('hourly_template_m');
        $this->load->model('make_payment_m');
        $this->load->model('usertype_m');
        $this->load->model('systemadmin_m');
        $this->load->model('teacher_m');
        $this->load->model('user_m');

        $this->calculator = new PayrollCalculator();
    }

    public function index(): void
    {
        $this->data['headerassets'] = [
            'css' => [
                'assets/select2/css/select2.css',
                'assets/select2/css/select2-bootstrap.css',
            ],
            'js' => [
                'assets/select2/select2.js',
            ],
        ];

        $schoolID = (int) $this->session->userdata('schoolID');
        $schoolyearID = (int) $this->session->userdata('defaultschoolyearID');

        $selectedRole = (int) $this->input->get('role');
        $selectedMonth = $this->input->get('month');
        if (empty($selectedMonth)) {
            $selectedMonth = date('Y-m');
        }

        $roles = $this->usertype_m->get_order_by_usertype_with_or(['schoolID' => $schoolID]);
        $roleMap = [];
        foreach ($roles as $role) {
            $roleMap[$role->usertypeID] = $role->usertype;
        }

        $dataset = $this->buildPayrollDataset($schoolID, $schoolyearID, $selectedRole, $selectedMonth, $roleMap);

        $this->data['roles'] = $roles;
        $this->data['selectedRole'] = $selectedRole;
        $this->data['selectedMonth'] = $selectedMonth;
        $this->data['payrollRows'] = $dataset['rows'];
        $this->data['payrollSummary'] = $dataset['summary'];
        $this->data['roleBreakdown'] = $dataset['roleBreakdown'];
        $this->data['payrollCsvUrl'] = site_url('payroll/export_csv?role=' . $selectedRole . '&month=' . $selectedMonth);

        $this->data['subview'] = 'payroll/index_v2';
        $this->load->view('_layout_main', $this->data);
    }

    public function export_csv(): void
    {
        $schoolID = (int) $this->session->userdata('schoolID');
        $schoolyearID = (int) $this->session->userdata('defaultschoolyearID');
        $role = (int) $this->input->get('role');
        $month = $this->input->get('month');
        if (empty($month)) {
            $month = date('Y-m');
        }

        $roles = $this->usertype_m->get_order_by_usertype_with_or(['schoolID' => $schoolID]);
        $roleMap = [];
        foreach ($roles as $roleRow) {
            $roleMap[$roleRow->usertypeID] = $roleRow->usertype;
        }

        $dataset = $this->buildPayrollDataset($schoolID, $schoolyearID, $role, $month, $roleMap);

        $filename = 'payroll_' . $month . '_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-store, no-cache');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['Name', 'Role', 'Salary Type', 'Base Salary', 'Allowances', 'Deductions', 'Estimated Net Pay', 'Paid This Month', 'Last Payment']);

        foreach ($dataset['rows'] as $row) {
            $allowanceTotal = array_sum(array_map(static function ($allowance) {
                return (float) ($allowance['amount'] ?? 0.0);
            }, $row['allowances']));

            $deductionTotal = array_sum(array_map(static function ($deduction) {
                return (float) ($deduction['amount'] ?? 0.0);
            }, $row['deductions']));

            fputcsv($output, [
                $row['user']['name'],
                $row['role_name'],
                ucfirst($row['salary_type']),
                number_format($row['base_salary'], 2),
                number_format($allowanceTotal, 2),
                number_format($deductionTotal, 2),
                number_format($row['summary']['net'], 2),
                number_format($row['paid_this_month'], 2),
                $row['last_paid_at'] ? date('Y-m-d', strtotime($row['last_paid_at'])) : '',
            ]);
        }

        fflush($output);
        fclose($output);
        exit;
    }

    protected function buildPayrollDataset(int $schoolID, int $schoolyearID, int $role, string $month, array $roleMap): array
    {
        $salaryFilters = ['schoolID' => $schoolID];
        if ($role > 0) {
            $salaryFilters['usertypeID'] = $role;
        }
        $manageSalaries = $this->manage_salary_m->get_order_by_manage_salary($salaryFilters);

        $paymentFilters = [
            'schoolID' => $schoolID,
            'schoolyearID' => $schoolyearID,
            'month' => $month,
        ];
        if ($role > 0) {
            $paymentFilters['usertypeID'] = $role;
        }

        $payments = $this->make_payment_m->get_order_by_make_payment($paymentFilters);
        $paymentLookup = [];
        $totalPayout = 0.0;
        foreach ($payments as $payment) {
            if (!empty($month) && $payment->month !== $month) {
                continue;
            }
            $key = $payment->usertypeID . ':' . $payment->userID;
            if (!isset($paymentLookup[$key])) {
                $paymentLookup[$key] = [
                    'total' => 0.0,
                    'last_payment' => null,
                ];
            }
            $paymentLookup[$key]['total'] += (float) $payment->paymentamount;
            $totalPayout += (float) $payment->paymentamount;
            if (!$paymentLookup[$key]['last_payment'] || $payment->create_date > $paymentLookup[$key]['last_payment']) {
                $paymentLookup[$key]['last_payment'] = $payment->create_date;
            }
        }

        $rows = [];
        $roleBreakdown = [];
        $netAccumulator = 0.0;
        $paidEmployees = 0;

        foreach ($manageSalaries as $entry) {
            $user = $this->resolveUserRecord((int) $entry->usertypeID, (int) $entry->userID, $schoolID);
            if (!$user) {
                continue;
            }

            $salaryType = (int) $entry->salary === 2 ? 'hourly' : 'monthly';
            $baseSalary = 0.0;
            $allowances = [];
            $deductions = [];
            $templateLabel = '';

            if ((int) $entry->salary === 1) {
                $template = $this->getSalaryTemplate((int) $entry->template, $schoolID);
                if ($template) {
                    $templateLabel = $template->salary_grades ?? '';
                    $baseSalary = (float) ($template->basic_salary ?? 0.0);
                    $options = $this->getTemplateOptions((int) $template->salary_templateID, $schoolID);
                    $allowances = $options['allowances'];
                    $deductions = $options['deductions'];
                }
            } elseif ((int) $entry->salary === 2) {
                $hourlyTemplate = $this->getHourlyTemplate((int) $entry->template, $schoolID);
                if ($hourlyTemplate) {
                    $templateLabel = $hourlyTemplate->hourly_grades ?? '';
                    $baseSalary = (float) ($hourlyTemplate->hourly_rate ?? 0.0);
                }
            }

            $allowanceAmounts = array_map(static function ($allowance) {
                return (float) ($allowance['amount'] ?? 0.0);
            }, $allowances);
            $deductionAmounts = array_map(static function ($deduction) {
                return (float) ($deduction['amount'] ?? 0.0);
            }, $deductions);

            $summary = $this->calculator->summarize($baseSalary, $allowanceAmounts, ['post_tax' => $deductionAmounts], 0.0);
            $netAccumulator += (float) $summary['net'];

            $paymentKey = $entry->usertypeID . ':' . $entry->userID;
            $paidThisMonth = isset($paymentLookup[$paymentKey]) ? (float) $paymentLookup[$paymentKey]['total'] : 0.0;
            if ($paidThisMonth > 0) {
                $paidEmployees++;
            }

            $roleBreakdown[$entry->usertypeID] = ($roleBreakdown[$entry->usertypeID] ?? 0) + 1;

            $rows[] = [
                'user' => [
                    'name' => $user->name ?? $user->username ?? '---',
                    'email' => $user->email ?? '',
                    'photo' => $user->photo ?? '',
                    'join_date' => $user->jod ?? '',
                ],
                'role_name' => $roleMap[$entry->usertypeID] ?? ('#' . $entry->usertypeID),
                'salary_type' => $salaryType,
                'template' => $templateLabel,
                'base_salary' => $baseSalary,
                'allowances' => $allowances,
                'deductions' => $deductions,
                'summary' => $summary,
                'paid_this_month' => $paidThisMonth,
                'last_paid_at' => $paymentLookup[$paymentKey]['last_payment'] ?? null,
            ];
        }

        $totalEmployees = count($rows);
        $outstanding = $totalEmployees - $paidEmployees;
        $averageNet = $totalEmployees > 0 ? round($netAccumulator / $totalEmployees, 2) : 0.0;

        $summary = [
            'total_employees' => $totalEmployees,
            'paid_employees' => $paidEmployees,
            'outstanding_employees' => max(0, $outstanding),
            'total_payout' => round($totalPayout, 2),
            'average_net' => $averageNet,
        ];

        return [
            'rows' => $rows,
            'summary' => $summary,
            'roleBreakdown' => $roleBreakdown,
        ];
    }

    protected function resolveUserRecord(int $usertypeID, int $userID, int $schoolID)
    {
        $key = $usertypeID . ':' . $userID;
        if (array_key_exists($key, $this->userCache)) {
            return $this->userCache[$key];
        }

        switch ($usertypeID) {
            case 1:
                $record = $this->systemadmin_m->get_single_systemadmin([
                    'systemadminID' => $userID,
                    'schoolID' => $schoolID,
                ]);
                break;
            case 2:
                $record = $this->teacher_m->get_single_teacher([
                    'teacherID' => $userID,
                    'schoolID' => $schoolID,
                ]);
                break;
            default:
                $record = $this->user_m->get_single_user([
                    'userID' => $userID,
                    'schoolID' => $schoolID,
                ]);
                break;
        }

        $this->userCache[$key] = $record ?: null;
        return $this->userCache[$key];
    }

    protected function getSalaryTemplate(int $templateID, int $schoolID)
    {
        if (!$templateID) {
            return null;
        }
        if (!array_key_exists($templateID, $this->salaryTemplateCache)) {
            $this->salaryTemplateCache[$templateID] = $this->salary_template_m->get_single_salary_template([
                'salary_templateID' => $templateID,
                'schoolID' => $schoolID,
            ]);
        }
        return $this->salaryTemplateCache[$templateID];
    }

    protected function getTemplateOptions(int $templateID, int $schoolID): array
    {
        if (!$templateID) {
            return ['allowances' => [], 'deductions' => []];
        }
        if (!array_key_exists($templateID, $this->templateOptionsCache)) {
            $options = $this->salaryoption_m->get_order_by_salaryoption([
                'salary_templateID' => $templateID,
                'schoolID' => $schoolID,
            ]);
            $grouped = ['allowances' => [], 'deductions' => []];
            foreach ($options as $option) {
                $entry = [
                    'label' => $option->label_name,
                    'amount' => (float) $option->label_amount,
                ];
                if ((int) $option->option_type === 1) {
                    $grouped['allowances'][] = $entry;
                } else {
                    $grouped['deductions'][] = $entry;
                }
            }
            $this->templateOptionsCache[$templateID] = $grouped;
        }
        return $this->templateOptionsCache[$templateID];
    }

    protected function getHourlyTemplate(int $templateID, int $schoolID)
    {
        if (!$templateID) {
            return null;
        }
        if (!array_key_exists($templateID, $this->hourlyTemplateCache)) {
            $this->hourlyTemplateCache[$templateID] = $this->hourly_template_m->get_single_hourly_template([
                'hourly_templateID' => $templateID,
                'schoolID' => $schoolID,
            ]);
        }
        return $this->hourlyTemplateCache[$templateID];
    }
}
