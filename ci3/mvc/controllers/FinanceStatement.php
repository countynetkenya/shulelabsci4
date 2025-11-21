<?php

defined('BASEPATH') or exit('No direct script access allowed');

class FinanceStatement extends Admin_Controller
{
    /** @var Studentstatementservice */
    protected $statementService;

    public function __construct()
    {
        parent::__construct();
        require_feature_flag('UNIFIED_STATEMENT');

        $language = $this->session->userdata('lang');
        $this->lang->load('menu', $language);
        $this->lang->load('finance_statement', $language);
        $this->lang->load('global_payment', $language);

        $pageTitle = lang('menu_unified_statement');
        if ($pageTitle === 'menu_unified_statement') {
            $pageTitle = 'Unified Statement';
        }

        $this->data['pageTitle'] = $pageTitle;

        $this->load->model('classes_m');
        $this->load->model('section_m');
        $this->load->model('studentrelation_m');
        $this->load->model('parents_m');
        $this->load->model('schoolyear_m');
        $this->load->model('schoolterm_m');
        $this->load->model('usertype_m');
        $this->load->library('studentstatementservice');

        $this->statementService = $this->studentstatementservice;
    }

    public function index(): void
    {
        $this->data['headerassets'] = [
            'css' => [
                'assets/datepicker/datepicker.css',
                'assets/select2/css/select2.css',
                'assets/select2/css/select2-bootstrap.css',
            ],
            'js' => [
                'assets/datepicker/datepicker.js',
                'assets/select2/select2.js',
            ],
        ];

        $schoolID = (int) $this->session->userdata('schoolID');
        $defaultSchoolYear = (int) $this->session->userdata('defaultschoolyearID');

        $this->data['classes'] = $this->classes_m->get_order_by_classes(['schoolID' => $schoolID]);
        $this->data['schoolYears'] = $this->schoolyear_m->get_order_by_schoolyear(['schoolID' => $schoolID]);
        $this->data['terms'] = $this->schoolterm_m->get_order_by_schoolterm([
            'schoolyearID' => $defaultSchoolYear,
            'schoolID' => $schoolID,
        ]);
        $this->data['roles'] = $this->usertype_m->get_order_by_usertype_with_or(['schoolID' => $schoolID]);

        $filters = $this->gatherStatementFilters();

        $this->populateFilterSelections($filters, $defaultSchoolYear);

        $this->data['financeStatementApiUrl'] = site_url('finance_statement/api');
        $this->data['financeStatementCsvUrl'] = site_url('finance_statement/export_csv');
        $this->data['financeStatementPdfUrl'] = site_url('finance_statement/export_pdf');

        $this->data['subview'] = 'finance/statement_index';
        $this->load->view('_layout_main', $this->data);
    }

    public function api(): void
    {
        $filters = $this->gatherStatementFilters();
        $payload = $this->statementService->build($filters);

        $summary = $this->buildSummary($payload);

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'status' => 'ok',
                'data' => $payload,
                'summary' => $summary,
            ]));
    }

    public function export_csv(): void
    {
        $filters = $this->gatherStatementFilters();
        $payload = $this->statementService->build($filters);

        $includeParentDetails = !empty($filters['includeParentDetails']);
        $includeStudentDetails = !empty($filters['includeStudentDetails']);

        $siteInfo = $this->data['siteinfos'] ?? null;
        $currencyCode = '';
        if ($siteInfo && isset($siteInfo->currency_code) && $siteInfo->currency_code) {
            $currencyCode = (string) $siteInfo->currency_code;
        }
        $currencySuffix = $currencyCode !== '' ? ' (' . $currencyCode . ')' : '';

        $filename = 'unified_statement_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-store, no-cache');

        $output = fopen('php://output', 'w');
        $header = ['Student ID', 'Admission Number', 'Student Name', 'Role', 'Class', 'Section', 'Group'];
        if ($includeStudentDetails) {
            $header = array_merge($header, [
                lang('finance_statement_student_address'),
                lang('finance_statement_student_email'),
                lang('finance_statement_student_phone'),
            ]);
        }
        if ($includeParentDetails) {
            $header = array_merge($header, ['Parent Name', 'Parent Email', 'Parent Phone', 'Parent Address']);
        }
        $header = array_merge($header, [
            'Date',
            'Description',
            lang('finance_statement_table_debit') . $currencySuffix,
            lang('finance_statement_table_credit') . $currencySuffix,
            lang('finance_statement_summary_period_balance_column') . $currencySuffix,
            lang('finance_statement_table_running_balance') . $currencySuffix,
            lang('finance_statement_summary_term_column'),
            lang('finance_statement_summary_year_column'),
            'Type',
        ]);
        fputcsv($output, $header);

        $studentColumnCount = 7;
        if ($includeStudentDetails) {
            $studentColumnCount += 3;
        }
        if ($includeParentDetails) {
            $studentColumnCount += 4;
        }

        foreach ($payload['students'] as $student) {
            $parent = $student['parent'] ?? [];
            $studentInfo = $student['student'] ?? [];
            foreach ($student['rows'] as $row) {
                $record = [
                    $studentInfo['studentID'] ?? '',
                    $studentInfo['admission_number'] ?? '',
                    $studentInfo['student_name'] ?? '',
                    $studentInfo['role'] ?? '',
                    $studentInfo['class'] ?? '',
                    $studentInfo['section'] ?? '',
                    $studentInfo['group'] ?? '',
                ];

                if ($includeStudentDetails) {
                    $record[] = $studentInfo['address'] ?? '';
                    $record[] = $studentInfo['email'] ?? '';
                    $record[] = $studentInfo['phone'] ?? '';
                }

                if ($includeParentDetails) {
                    $record[] = $parent['name'] ?? '';
                    $record[] = $parent['email'] ?? '';
                    $record[] = $parent['phone'] ?? '';
                    $record[] = $parent['address'] ?? '';
                }

                $record[] = $row['day'] ?? '';
                $record[] = $row['description'] ?? '';
                $record[] = $row['debit'] ?? 0;
                $record[] = $row['credit'] ?? 0;
                $record[] = '';
                $record[] = $row['running_balance'] ?? ($row['balance'] ?? 0);
                $record[] = $row['term_label'] ?? ($row['term'] ?? '');
                $record[] = $row['schoolyear_label'] ?? '';
                $record[] = $row['type'] ?? '';

                fputcsv($output, $record);
            }

            $summary = $student['balance_summary'] ?? [];
            if (!empty($summary)) {
                $prefix = array_fill(0, $studentColumnCount, '');
                fputcsv($output, []);

                fputcsv($output, array_merge($prefix, [
                    '',
                    lang('finance_statement_opening_balance'),
                    '',
                    '',
                    '',
                    $summary['opening_balance'] ?? 0,
                    '',
                    '',
                    'opening_balance',
                ]));

                foreach ($summary['terms'] ?? [] as $termSummary) {
                    $label = $termSummary['label'] ?? '';
                    $periodBalance = $termSummary['period_balance'] ?? (($termSummary['debit'] ?? 0) - ($termSummary['credit'] ?? 0));
                    fputcsv($output, array_merge($prefix, [
                        '',
                        sprintf('%s: %s', lang('finance_statement_summary_term_heading'), $label),
                        $termSummary['debit'] ?? 0,
                        $termSummary['credit'] ?? 0,
                        $periodBalance,
                        $termSummary['balance'] ?? 0,
                        $label,
                        $termSummary['schoolyear_label'] ?? '',
                        'term_summary',
                    ]));
                }

                foreach ($summary['academic_years'] ?? [] as $yearSummary) {
                    $yearLabel = $yearSummary['label'] ?? '';
                    $yearPeriodBalance = $yearSummary['period_balance'] ?? (($yearSummary['debit'] ?? 0) - ($yearSummary['credit'] ?? 0));
                    fputcsv($output, array_merge($prefix, [
                        '',
                        sprintf('%s: %s', lang('finance_statement_summary_year_heading'), $yearLabel),
                        $yearSummary['debit'] ?? 0,
                        $yearSummary['credit'] ?? 0,
                        $yearPeriodBalance,
                        $yearSummary['balance'] ?? 0,
                        '',
                        $yearLabel,
                        'year_summary',
                    ]));
                }

                $totalsPeriodBalance = ($summary['total_debit'] ?? 0) - ($summary['total_credit'] ?? 0);
                fputcsv($output, array_merge($prefix, [
                    '',
                    lang('finance_statement_summary_totals'),
                    $summary['total_debit'] ?? 0,
                    $summary['total_credit'] ?? 0,
                    $totalsPeriodBalance,
                    '',
                    '',
                    '',
                    'totals',
                ]));

                fputcsv($output, array_merge($prefix, [
                    '',
                    lang('finance_statement_summary_closing_balance'),
                    '',
                    '',
                    '',
                    $summary['closing_balance'] ?? 0,
                    '',
                    '',
                    'closing_balance',
                ]));
            }
        }

        fflush($output);
        fclose($output);
        exit;
    }

    public function export_pdf(): void
    {
        $filters = $this->gatherStatementFilters();
        $payload = $this->statementService->build($filters);

        $summary = $this->buildSummary($payload);
        $generatedAtIso = $summary['generated_at'] ?? ($payload['generated_at'] ?? null);
        $generatedAtDisplay = $this->formatStatementDateTime($generatedAtIso);
        $summary['generated_at_display'] = $generatedAtDisplay;
        $summary['generated_by'] = $summary['generated_by'] ?? '';

        $students = $payload['students'] ?? [];
        foreach ($students as &$student) {
            $range = isset($student['range']) && is_array($student['range']) ? $student['range'] : [];
            $student['range_display'] = $this->formatStatementRange($range);
        }
        unset($student);

        $payload['students'] = $students;
        $payload['generated_at_display'] = $generatedAtDisplay;

        $this->data['statementPayload'] = $payload;
        $this->data['summary'] = $summary;
        $this->data['generatedAtDisplay'] = $generatedAtDisplay;
        $this->data['generatedByDisplay'] = $summary['generated_by'];

        $siteInfo = $this->data['siteinfos'] ?? null;
        $currencySymbol = ($siteInfo && !empty($siteInfo->currency_symbol)) ? trim((string) $siteInfo->currency_symbol) : '';
        $currencyCode = ($siteInfo && !empty($siteInfo->currency_code)) ? trim((string) $siteInfo->currency_code) : '';
        $currencyDisplay = trim(($currencySymbol !== '' ? $currencySymbol . ' ' : '') . $currencyCode);

        $headerHtml = $this->load->view('finance/partials/statement_pdf_header', [
            'siteinfos' => $siteInfo,
            'currencyDisplay' => $currencyDisplay,
            'generatedAtDisplay' => $generatedAtDisplay,
            'generatedByDisplay' => $summary['generated_by'],
        ], true);

        $footerHtml = $this->load->view('finance/partials/statement_pdf_footer', [
            'pageLabel' => $this->lang->line('finance_statement_page_label'),
            'pageOfLabel' => $this->lang->line('finance_statement_page_of_label'),
        ], true);

        $this->reportPDF('invoicemodule.css', $this->data, 'finance/statement_export_pdf', 'view', 'a4', 'portrait', $headerHtml, $footerHtml);
    }

    public function sectioncall(): void
    {
        $classesID = (int) $this->input->post('id');
        $schoolID = (int) $this->session->userdata('schoolID');

        $label = $this->lang->line('global_select_section');
        $label = $label === 'global_select_section' ? 'Select section' : $label;

        $options = [sprintf("<option value='0'>%s</option>", htmlspecialchars($label, ENT_QUOTES, 'UTF-8'))];

        if ($classesID > 0) {
            $sections = $this->section_m->get_order_by_section([
                'classesID' => $classesID,
                'schoolID' => $schoolID,
            ]);

            foreach ($sections as $section) {
                $options[] = sprintf(
                    '<option value="%d">%s</option>',
                    (int) $section->sectionID,
                    htmlspecialchars((string) $section->section, ENT_QUOTES, 'UTF-8')
                );
            }
        }

        $this->output
            ->set_content_type('text/html', 'UTF-8')
            ->set_output(implode('', $options));
    }

    public function studentcall(): void
    {
        $classesID = (int) $this->input->post('classesID');
        $sectionID = (int) $this->input->post('sectionID');
        $schoolyearID = (int) $this->session->userdata('defaultschoolyearID');
        $schoolID = (int) $this->session->userdata('schoolID');
        $usertypeID = (int) $this->session->userdata('usertypeID');
        $userID = (int) $this->session->userdata('loginuserID');

        $label = $this->lang->line('global_select_student');
        $label = $label === 'global_select_student' ? 'Select student' : $label;

        $options = [sprintf("<option value='0'>%s</option>", htmlspecialchars($label, ENT_QUOTES, 'UTF-8'))];

        if ($classesID > 0) {
            $filters = [
                'srclassesID' => $classesID,
                'srschoolyearID' => $schoolyearID,
                'srschoolID' => $schoolID,
            ];

            if ($sectionID > 0) {
                $filters['srsectionID'] = $sectionID;
            }

            if ($usertypeID === 3) {
                $filters['srstudentID'] = $userID;
            } elseif ($usertypeID === 4) {
                $filters['parentID'] = $userID;
            }

            $students = $this->studentrelation_m->get_order_by_student($filters);
            $registerLabel = $this->lang->line('global_register_no');
            $registerLabel = $registerLabel === 'global_register_no' ? 'Register No' : $registerLabel;
            foreach ($students as $student) {
                $name = isset($student->srname) ? (string) $student->srname : '';
                $studentID = isset($student->srstudentID) ? (int) $student->srstudentID : 0;
                $displayName = trim($name);
                if ($displayName === '') {
                    $displayName = (string) $studentID;
                }
                $registerNumber = '';
                if (isset($student->srregisterNO)) {
                    $registerNumber = trim((string) $student->srregisterNO);
                }
                if ($registerNumber === '') {
                    $registerNumber = (string) $studentID;
                }

                $optionLabel = sprintf('%s - %s - %s', $displayName, $registerLabel, $registerNumber);
                $options[] = sprintf(
                    '<option value="%d">%s</option>',
                    $studentID,
                    htmlspecialchars($optionLabel, ENT_QUOTES, 'UTF-8')
                );
            }
        }

        $this->output
            ->set_content_type('text/html', 'UTF-8')
            ->set_output(implode('', $options));
    }

    public function parentcall(): void
    {
        $classesID = (int) $this->input->post('classesID');
        $sectionID = (int) $this->input->post('sectionID');
        $schoolyearID = (int) $this->session->userdata('defaultschoolyearID');
        $schoolID = (int) $this->session->userdata('schoolID');

        $label = $this->lang->line('global_select_parent');
        $label = $label === 'global_select_parent' ? 'Select parent' : $label;

        $options = [sprintf("<option value='0'>%s</option>", htmlspecialchars($label, ENT_QUOTES, 'UTF-8'))];

        if ($classesID > 0) {
            $filters = [
                'srclassesID' => $classesID,
                'srschoolyearID' => $schoolyearID,
                'srschoolID' => $schoolID,
            ];

            if ($sectionID > 0) {
                $filters['srsectionID'] = $sectionID;
            }

            $students = $this->studentrelation_m->get_order_by_student($filters);
            $parentIDs = [];

            foreach ($students as $student) {
                $parentID = isset($student->parentID) ? (int) $student->parentID : 0;
                if ($parentID > 0) {
                    $parentIDs[$parentID] = true;
                }
            }

            if (!empty($parentIDs)) {
                $uniqueParentIDs = array_map('intval', array_keys($parentIDs));
                sort($uniqueParentIDs);

                $parents = $this->parents_m->get_parents_wherein($uniqueParentIDs);
                $parentOptions = [];

                foreach ($parents as $parent) {
                    $parentID = isset($parent->parentsID) ? (int) $parent->parentsID : 0;
                    if ($parentID <= 0) {
                        continue;
                    }

                    if (isset($parent->schoolID) && (int) $parent->schoolID !== $schoolID) {
                        continue;
                    }

                    $name = isset($parent->name) ? trim((string) $parent->name) : '';
                    if ($name === '') {
                        $name = (string) $parentID;
                    }

                    $parentOptions[$parentID] = $name;
                }

                if (!empty($parentOptions)) {
                    asort($parentOptions, SORT_NATURAL | SORT_FLAG_CASE);

                    foreach ($parentOptions as $parentID => $name) {
                        $options[] = sprintf(
                            '<option value="%d">%s</option>',
                            (int) $parentID,
                            htmlspecialchars($name, ENT_QUOTES, 'UTF-8')
                        );
                    }
                }
            }
        }

        $this->output
            ->set_content_type('text/html', 'UTF-8')
            ->set_output(implode('', $options));
    }

    public function termcall(): void
    {
        $schoolYearID = (int) $this->input->post('schoolYearID');
        $schoolID = (int) $this->session->userdata('schoolID');

        $label = $this->lang->line('global_select_schoolterm');
        $label = $label === 'global_select_schoolterm' ? 'Select term' : $label;

        $options = [sprintf("<option value='0'>%s</option>", htmlspecialchars($label, ENT_QUOTES, 'UTF-8'))];

        if ($schoolYearID > 0) {
            $terms = $this->schoolterm_m->get_order_by_schoolterm([
                'schoolyearID' => $schoolYearID,
                'schoolID' => $schoolID,
            ]);

            foreach ($terms as $term) {
                $options[] = sprintf(
                    '<option value="%d">%s</option>',
                    (int) $term->schooltermID,
                    htmlspecialchars((string) $term->schooltermtitle, ENT_QUOTES, 'UTF-8')
                );
            }
        }

        $this->output
            ->set_content_type('text/html', 'UTF-8')
            ->set_output(implode('', $options));
    }

    public function datescall(): void
    {
        $schooltermID = (int) $this->input->post('schooltermID');
        $schoolID = (int) $this->session->userdata('schoolID');

        $term = (object) ['startingdate' => '', 'endingdate' => ''];
        if ($schooltermID > 0) {
            $termRecord = $this->schoolterm_m->get_single_schoolterm([
                'schooltermID' => $schooltermID,
                'schoolID' => $schoolID,
            ]);

            if ($termRecord) {
                $term = $termRecord;
            }
        }

        $this->output
            ->set_content_type('application/json', 'UTF-8')
            ->set_output(json_encode($term));
    }

    protected function populateFilterSelections(array $filters, int $defaultSchoolYear): void
    {
        $schoolID = (int) $this->session->userdata('schoolID');
        $usertypeID = (int) $this->session->userdata('usertypeID');
        $loginUserID = (int) $this->session->userdata('loginuserID');

        $classesID = $filters['classesID'] ?? 0;
        $sectionID = $filters['sectionID'] ?? 0;
        $studentID = $filters['studentID'] ?? 0;
        $parentID = $filters['parentID'] ?? 0;
        $roleID = $filters['roleID'] ?? 0;
        $schoolyearID = $filters['schoolyearID'] ?? $defaultSchoolYear;

        $this->data['set_classesID'] = $classesID;
        $this->data['set_sectionID'] = $sectionID;
        $this->data['set_studentID'] = $studentID;
        $this->data['set_parentID'] = $parentID;
        $this->data['set_roleID'] = $roleID;
        $this->data['set_schoolYearID'] = $schoolyearID;
        $this->data['set_schooltermID'] = $filters['schooltermID'] ?? 0;
        $this->data['set_dateFrom'] = $filters['dateFrom'] ?? '';
        $this->data['set_dateTo'] = $filters['dateTo'] ?? '';
        $this->data['set_month'] = $filters['month'] ?? '';
        $this->data['set_specificDate'] = $filters['specificDate'] ?? '';
        $this->data['set_includeParentDetails'] = array_key_exists('includeParentDetails', $filters)
            ? (bool) $filters['includeParentDetails']
            : true;
        $this->data['set_includeStudentDetails'] = array_key_exists('includeStudentDetails', $filters)
            ? (bool) $filters['includeStudentDetails']
            : true;

        $this->data['sections'] = [];
        $this->data['students'] = [];
        $this->data['parents'] = [];
        $this->data['usertypeID'] = $usertypeID;

        if ($classesID > 0) {
            $this->data['sections'] = $this->section_m->get_order_by_section([
                'classesID' => $classesID,
                'schoolID' => $schoolID,
            ]);
        }

        if ($usertypeID === 3) {
            $this->data['students'] = $this->studentrelation_m->general_get_order_by_student(['studentID' => $loginUserID]);
        } elseif ($usertypeID === 4) {
            $this->data['students'] = $this->studentrelation_m->general_get_order_by_student(['parentID' => $loginUserID]);
            $this->data['parents'] = $this->parents_m->get_parents_wherein(['parentsID' => $loginUserID]);
        } else {
            $studentFilters = ['srschoolID' => $schoolID];
            if ($schoolyearID > 0) {
                $studentFilters['srschoolyearID'] = $schoolyearID;
            }
            if ($classesID > 0) {
                $studentFilters['srclassesID'] = $classesID;
            }
            if ($sectionID > 0) {
                $studentFilters['srsectionID'] = $sectionID;
            }

            $this->data['students'] = $this->studentrelation_m->get_order_by_student($studentFilters);

            $parentIDs = [];
            foreach ($this->data['students'] as $student) {
                if (!in_array($student->parentID, $parentIDs, true)) {
                    $parentIDs[] = $student->parentID;
                }
            }
            if (!empty($parentIDs)) {
                $this->data['parents'] = $this->parents_m->get_parents_wherein($parentIDs);
            }
        }
    }

    protected function buildSummary(array $payload): array
    {
        $totalStudents = 0;
        $totalBalance = 0.0;
        $totalDebit = 0.0;
        $totalCredit = 0.0;

        foreach ($payload['students'] as $student) {
            $totalStudents++;
            $totalBalance += (float) ($student['student']['closing_balance'] ?? 0.0);
            $totalDebit += (float) ($student['student']['total_debit'] ?? 0.0);
            $totalCredit += (float) ($student['student']['total_credit'] ?? 0.0);
        }

        $generatedAt = $payload['generated_at'] ?? date(DATE_ATOM);
        $generatedBy = $this->session->userdata('name');
        if (empty($generatedBy)) {
            $generatedBy = $this->session->userdata('username');
        }

        return [
            'total_students' => $totalStudents,
            'total_balance' => round($totalBalance, 2),
            'total_debit' => round($totalDebit, 2),
            'total_credit' => round($totalCredit, 2),
            'generated_at' => $generatedAt,
            'generated_by' => $generatedBy,
        ];
    }

    protected function formatStatementDateTime(?string $value): string
    {
        if (empty($value)) {
            return '';
        }

        try {
            $date = new \DateTimeImmutable($value);
        } catch (\Exception $exception) {
            return '';
        }

        return $date->format('m/d/Y, g:i:s A');
    }

    protected function formatStatementDate(?string $value): string
    {
        if (empty($value)) {
            return '';
        }

        try {
            $date = new \DateTimeImmutable($value);
        } catch (\Exception $exception) {
            return '';
        }

        return $date->format('m/d/Y');
    }

    protected function formatStatementRange($range): string
    {
        if (!is_array($range)) {
            return '';
        }

        $from = $this->formatStatementDate($range['from'] ?? null);
        $to = $this->formatStatementDate($range['to'] ?? null);

        if ($from && $to) {
            $separator = $this->lang->line('finance_statement_range_separator');
            if ($separator === 'finance_statement_range_separator') {
                $separator = 'to';
            }

            return $from . ' ' . $separator . ' ' . $to;
        }

        return $from ?: $to;
    }

    protected function gatherStatementFilters(): array
    {
        $filters = [
            'classesID' => (int) $this->input->get_post('classesID'),
            'sectionID' => (int) $this->input->get_post('sectionID'),
            'studentID' => (int) $this->input->get_post('studentID'),
            'parentID' => (int) $this->input->get_post('parentID'),
            'roleID' => (int) $this->input->get_post('roleID'),
            'schoolyearID' => (int) $this->input->get_post('schoolYearID'),
            'schooltermID' => (int) $this->input->get_post('schooltermID'),
            'dateFrom' => $this->input->get_post('dateFrom'),
            'dateTo' => $this->input->get_post('dateTo'),
            'month' => $this->input->get_post('month'),
            'specificDate' => $this->input->get_post('specificDate'),
            'schoolID' => (int) $this->session->userdata('schoolID'),
            'usertypeID' => (int) $this->session->userdata('usertypeID'),
            'loginuserID' => (int) $this->session->userdata('loginuserID'),
        ];

        $filters['includeParentDetails'] = $this->parseBoolean($this->input->get_post('includeParentDetails'), true);
        $filters['includeStudentDetails'] = $this->parseBoolean($this->input->get_post('includeStudentDetails'), true);

        if ($filters['schoolyearID'] === 0) {
            $filters['schoolyearID'] = (int) $this->session->userdata('defaultschoolyearID');
        }

        if (empty($filters['dateFrom'])) {
            $filters['dateFrom'] = $this->input->get_post('set_dateFrom');
        }
        if (empty($filters['dateTo'])) {
            $filters['dateTo'] = $this->input->get_post('set_dateTo');
        }

        if ($filters['usertypeID'] === 3) {
            $filters['studentID'] = (int) $this->session->userdata('loginuserID');
            $filters['parentID'] = 0;
            $filters['roleID'] = 0;
        } elseif ($filters['usertypeID'] === 4) {
            $filters['parentID'] = (int) $this->session->userdata('loginuserID');
            $filters['studentID'] = 0;
            $filters['roleID'] = 0;
        }

        return $filters;
    }

    protected function parseBoolean($value, bool $default = true): bool
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
}
