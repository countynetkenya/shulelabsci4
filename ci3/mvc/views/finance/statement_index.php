<?php
$title = lang('menu_unified_statement');
if ($title === 'menu_unified_statement') {
    $title = 'Unified Statement';
}

$summaryTitle = lang('finance_statement_summary');
if ($summaryTitle === 'finance_statement_summary') {
    $summaryTitle = 'Statement summary';
}

$filtersLabel = lang('finance_statement_filters');
if ($filtersLabel === 'finance_statement_filters') {
    $filtersLabel = 'Filters';
}

$activeRoleID = set_value('roleID', $set_roleID);
$isStudentRole = ($activeRoleID == 3);
$isParentRole = ($activeRoleID == 4);
$includeParentDetails = !empty($set_includeParentDetails);
$includeStudentDetails = !empty($set_includeStudentDetails);
$currencyCode = isset($siteinfos->currency_code) ? trim((string) $siteinfos->currency_code) : '';
$currencySymbol = isset($siteinfos->currency_symbol) ? trim((string) $siteinfos->currency_symbol) : '';
$currencyDisplay = trim(($currencySymbol !== '' ? $currencySymbol . ' ' : '') . $currencyCode);

$letterheadDetails = [];
$address = isset($siteinfos->address) ? trim((string) $siteinfos->address) : '';
if ($address !== '') {
    $letterheadDetails[] = preg_replace('/\s+/', ' ', $address);
}

$phone = isset($siteinfos->phone) ? trim((string) $siteinfos->phone) : '';
if ($phone !== '') {
    $letterheadDetails[] = trim(lang('global_phone_number')) . ': ' . $phone;
}

$email = isset($siteinfos->email) ? trim((string) $siteinfos->email) : '';
if ($email !== '') {
    $letterheadDetails[] = trim(lang('global_email')) . ': ' . $email;
}

if ($currencyDisplay !== '') {
    $letterheadDetails[] = trim(lang('finance_statement_currency_label')) . ': ' . $currencyDisplay;
}

$letterheadDetailsLine = trim(implode(' • ', array_filter($letterheadDetails, static function ($value) {
    return $value !== '';
})));
?>

<style>
    .statement-letterhead {
        border-bottom: 1px solid #ddd;
        margin-bottom: 20px;
        padding-bottom: 15px;
    }
    .statement-letterhead .statement-logo {
        max-height: 60px;
        max-width: 60px;
    }
    .statement-letterhead h4 {
        margin-top: 0;
        font-weight: 600;
    }
    .statement-letterhead p {
        margin-bottom: 4px;
    }
    .statement-letterhead-details {
        color: #777;
        margin-bottom: 0;
        line-height: 1.5;
        overflow-wrap: anywhere;
        word-break: break-word;
        white-space: normal;
    }
    .statement-student-meta p {
        margin-bottom: 6px;
    }
    .statement-term-heading {
        margin-top: 20px;
        font-weight: 600;
        text-transform: uppercase;
    }
    .statement-term-subtotal td {
        font-weight: 600;
        background-color: #f7f7f9;
    }
    .statement-summary-block {
        margin-top: 20px;
    }
    .statement-summary-heading {
        font-weight: 600;
        margin-bottom: 10px;
    }
    .statement-summary-table th,
    .statement-summary-table td {
        vertical-align: middle;
    }
    .statement-toggle-group {
        margin-top: 25px;
    }
    #statement-footer {
        margin-top: 30px;
    }
    .statement-invoice {
        border: 1px solid #e5e5e5;
        padding: 20px;
        margin-bottom: 30px;
        background-color: #fff;
        box-shadow: 0 1px 1px rgba(0, 0, 0, 0.05);
    }
    .statement-invoice .page-header {
        margin: 0 0 20px;
        padding-bottom: 10px;
        border-bottom: 1px solid #f4f4f4;
    }
    .statement-invoice-logo {
        width: 40px;
        height: 40px;
        margin-right: 10px;
    }
    .statement-invoice h4 {
        margin-top: 25px;
        font-weight: 600;
    }
    .statement-terms table {
        margin-bottom: 20px;
    }
    .statement-page-break {
        display: none;
    }
    .statement-print-footer {
        display: none;
        text-align: right;
        color: #777;
        font-size: 12px;
    }
    .statement-body-content {
        position: relative;
    }
    @media print {
        .statement-invoice {
            page-break-inside: avoid;
        }
        .statement-page-break {
            display: block;
            page-break-after: always;
            height: 0;
            overflow: hidden;
        }
        #statement-letterhead {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            background-color: #fff;
            padding-bottom: 10px;
        }
        .statement-body-content {
            margin-top: 180px;
            padding-bottom: 80px;
        }
        .statement-print-footer {
            display: block;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 10px 20px;
            background-color: #fff;
        }
        .statement-print-footer::after {
            content: attr(data-page-label) ' ' counter(page) ' ' attr(data-of-label) ' ' counter(pages);
        }
    }
</style>

<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-balance-scale"></i> <?= html_escape($title); ?></h3>
        <div class="box-tools pull-right">
            <button type="button" id="statement-download-pdf" class="btn btn-default btn-sm" disabled>
                <i class="fa fa-file-pdf-o"></i> <?= html_escape(lang('finance_statement_export_pdf')); ?>
            </button>
            <button type="button" id="statement-download-csv" class="btn btn-default btn-sm" disabled>
                <i class="fa fa-download"></i> <?= html_escape(lang('finance_statement_export_csv')); ?>
            </button>
        </div>
    </div>
    <div class="box-body">
        <?php if (isset($siteinfos)): ?>
            <div id="statement-letterhead" class="statement-letterhead media">
                <?php if (!empty($siteinfos->photo)): ?>
                    <div class="media-left">
                        <img src="<?= base_url('uploads/images/' . $siteinfos->photo); ?>" alt="<?= html_escape($siteinfos->sname); ?>" class="img-circle statement-logo">
                    </div>
                <?php endif; ?>
                <div class="media-body">
                    <h4 class="media-heading"><?= html_escape($siteinfos->sname); ?></h4>
                    <?php if ($letterheadDetailsLine !== ''): ?>
                        <p class="statement-letterhead-details"><?= html_escape($letterheadDetailsLine); ?></p>
                    <?php endif; ?>
                </div>
                <div class="media-right text-right">
                    <p id="statement-generated-at" class="text-muted small"></p>
                    <p id="statement-generated-by" class="text-muted small"></p>
                </div>
            </div>
        <?php endif; ?>

        <div class="statement-body-content">
        <h4 class="m-t-0"><?= html_escape($filtersLabel); ?></h4>
        <form id="finance-statement-filter" class="mb-20" method="get">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="roleID" class="control-label"><?= html_escape(lang('finance_statement_role_label')); ?></label>
                        <?php
                            $roleArray = ['0' => lang('finance_statement_role_all')];
                            if (customCompute($roles)) {
                                foreach ($roles as $role) {
                                    $roleArray[$role->usertypeID] = $role->usertype;
                                }
                            }
                            echo form_dropdown('roleID', $roleArray, $activeRoleID, "id='roleID' class='form-control select2'");
                        ?>
                    </div>
                </div>
                <div class="col-md-3" id="filter-schoolyear-wrapper" <?= $isStudentRole ? '' : 'style="display:none;"'; ?>>
                    <div class="form-group">
                        <label for="schoolYearID" class="control-label"><?= $this->lang->line('global_schoolyear'); ?></label>
                        <?php
                            $schoolYearArray = ['0' => $this->lang->line('global_select_schoolyear')];
                            if (customCompute($schoolYears)) {
                                foreach ($schoolYears as $schoolYear) {
                                    $schoolYearArray[$schoolYear->schoolyearID] = $schoolYear->schoolyear;
                                }
                            }
                            echo form_dropdown('schoolYearID', $schoolYearArray, set_value('schoolYearID', $set_schoolYearID), "id='schoolYearID' class='form-control select2'");
                        ?>
                    </div>
                </div>
                <div class="col-md-3" id="filter-term-wrapper" <?= $isStudentRole ? '' : 'style="display:none;"'; ?>>
                    <div class="form-group">
                        <label for="schooltermID" class="control-label"><?= $this->lang->line('global_schooltermID'); ?></label>
                        <?php
                            $termArray = ['0' => $this->lang->line('global_select_schoolterm')];
                            if (customCompute($terms)) {
                                foreach ($terms as $term) {
                                    $termArray[$term->schooltermID] = $term->schooltermtitle;
                                }
                            }
                            echo form_dropdown('schooltermID', $termArray, set_value('schooltermID', $set_schooltermID), "id='schooltermID' class='form-control select2'");
                        ?>
                    </div>
                </div>
                <div class="col-md-3" id="filter-month-wrapper">
                    <div class="form-group">
                        <label for="month" class="control-label"><?= $this->lang->line('global_month'); ?></label>
                        <input type="month" id="month" name="month" class="form-control" value="<?= html_escape($set_month); ?>">
                    </div>
                </div>
            </div>

            <div class="row" id="student-filter-row" <?= $isStudentRole ? '' : 'style="display:none;"'; ?>>
                <div class="col-md-4 col-sm-6">
                    <div class="<?= form_error('classesID') ? 'form-group has-error' : 'form-group'; ?>">
                        <label for="classesID" class="control-label"><?= $this->lang->line('global_classes'); ?></label>
                        <?php
                            $classArray = ['0' => $this->lang->line('global_select_classes')];
                            if (customCompute($classes)) {
                                foreach ($classes as $class) {
                                    $classArray[$class->classesID] = $class->classes;
                                }
                            }
                            echo form_dropdown('classesID', $classArray, set_value('classesID', $set_classesID), "id='classesID' class='form-control select2'");
                        ?>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="<?= form_error('sectionID') ? 'form-group has-error' : 'form-group'; ?>">
                        <label for="sectionID" class="control-label"><?= $this->lang->line('global_section'); ?></label>
                        <?php
                            $sectionArray = ['0' => $this->lang->line('global_select_section')];
                            if (customCompute($sections)) {
                                foreach ($sections as $section) {
                                    $sectionArray[$section->sectionID] = $section->section;
                                }
                            }
                            echo form_dropdown('sectionID', $sectionArray, set_value('sectionID', $set_sectionID), "id='sectionID' class='form-control select2'");
                        ?>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6">
                    <div class="<?= form_error('studentID') ? 'form-group has-error' : 'form-group'; ?>">
                        <label for="studentID" class="control-label"><?= $this->lang->line('global_student'); ?></label>
                        <?php
                            $studentArray = ['0' => $this->lang->line('global_select_student')];
                            if (customCompute($students)) {
                                foreach ($students as $student) {
                                    $studentArray[$student->srstudentID] = $student->srname . ' - ' . $this->lang->line('global_register_no') . ' - ' . $student->srstudentID;
                                }
                            }
                            echo form_dropdown('studentID', $studentArray, set_value('studentID', $set_studentID), "id='studentID' class='form-control select2'");
                        ?>
                    </div>
                </div>
            </div>

            <?php if ($usertypeID != 4 && $usertypeID != 3): ?>
                <div class="row" id="parent-filter-row" <?= $isParentRole ? '' : 'style="display:none;"'; ?>>
                    <div class="col-md-3 col-sm-6" id="filter-parent-wrapper">
                        <div class="<?= form_error('parentID') ? 'form-group has-error' : 'form-group'; ?>">
                            <label for="parentID" class="control-label"><?= $this->lang->line('global_parent'); ?></label>
                            <?php
                                $parentArray = ['0' => $this->lang->line('global_select_parent')];
                                if (customCompute($parents)) {
                                    foreach ($parents as $parent) {
                                        $parentArray[$parent->parentsID] = $parent->name;
                                    }
                                }
                                echo form_dropdown('parentID', $parentArray, set_value('parentID', $set_parentID), "id='parentID' class='form-control select2'");
                            ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="row" id="date-filter-row">
                <div class="col-md-3" id="filter-day-wrapper">
                    <div class="form-group">
                        <label for="specificDate" class="control-label"><?= $this->lang->line('global_day'); ?></label>
                        <input type="date" id="specificDate" name="specificDate" class="form-control" value="<?= html_escape($set_specificDate); ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="dateFrom" class="control-label"><?= $this->lang->line('global_from'); ?></label>
                        <input type="date" id="dateFrom" name="dateFrom" class="form-control" value="<?= html_escape($set_dateFrom); ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="dateTo" class="control-label"><?= $this->lang->line('global_to'); ?></label>
                        <input type="date" id="dateTo" name="dateTo" class="form-control" value="<?= html_escape($set_dateTo); ?>">
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 col-xs-12">
                    <div class="form-group">
                        <label class="control-label" style="visibility:hidden;display:block;">&nbsp;</label>
                        <button type="submit" class="btn btn-success btn-block"><?= $this->lang->line('global_payment_search'); ?></button>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 col-sm-6 statement-toggle-group">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" id="includeParentDetails" name="includeParentDetails" value="1" <?= $includeParentDetails ? 'checked' : ''; ?>>
                            <?= html_escape(lang('finance_statement_include_parent_details')); ?>
                        </label>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 statement-toggle-group">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" id="includeStudentDetails" name="includeStudentDetails" value="1" <?= $includeStudentDetails ? 'checked' : ''; ?>>
                            <?= html_escape(lang('finance_statement_include_student_details')); ?>
                        </label>
                    </div>
                </div>
            </div>
        </form>


        <h4><?= html_escape($summaryTitle); ?></h4>
        <div class="row" id="statement-summary">
            <div class="col-md-3">
                <div class="small-box bg-aqua">
                    <div class="inner">
                        <h3 id="summary-total-students">0</h3>
                        <p><?= html_escape(lang('finance_statement_total_students')); ?></p>
                    </div>
                    <div class="icon"><i class="fa fa-users"></i></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-green">
                    <div class="inner">
                        <h3 id="summary-total-balance">0.00</h3>
                        <p><?= html_escape(lang('finance_statement_total_balance')); ?></p>
                    </div>
                    <div class="icon"><i class="fa fa-calculator"></i></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-yellow">
                    <div class="inner">
                        <h3 id="summary-total-debit">0.00</h3>
                        <p><?= html_escape(lang('finance_statement_total_debit')); ?></p>
                    </div>
                    <div class="icon"><i class="fa fa-arrow-circle-up"></i></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-red">
                    <div class="inner">
                        <h3 id="summary-total-credit">0.00</h3>
                        <p><?= html_escape(lang('finance_statement_total_credit')); ?></p>
                    </div>
                    <div class="icon"><i class="fa fa-arrow-circle-down"></i></div>
                </div>
            </div>
        </div>

        <div id="statement-status" class="alert alert-info" style="display:none;"></div>
        <div id="statement-row-count" class="text-muted m-b-10"></div>
        <div id="statement-table-container"></div>
        <div id="statement-footer" class="panel panel-default" style="display:none;">
            <div class="panel-body">
                <p id="statement-footer-text" class="m-b-5"></p>
                <p class="text-muted small m-b-0"><?= html_escape(lang('finance_statement_footer_note')); ?></p>
            </div>
        </div>
        <template id="statement-student-template">
            <section class="statement-invoice content invoice">
                <div class="row">
                    <div class="col-xs-12">
                        <h2 class="page-header">
                            <img class="statement-invoice-logo img-circle" src="" alt="" style="display:none;">
                            <span data-field="school-name"></span>
                            <small class="pull-right" data-field="generated-at"></small>
                        </h2>
                    </div>
                </div>
                <div class="row invoice-info">
                    <div class="col-sm-6 invoice-col">
                        <address data-field="student-block"></address>
                    </div>
                    <div class="col-sm-6 invoice-col">
                        <address data-field="parent-block"></address>
                    </div>
                </div>
                <div class="statement-terms"></div>
                <div class="statement-summary-container"></div>
                <div class="row">
                    <div class="col-xs-12 text-right">
                        <p><small data-field="footer-meta"></small></p>
                    </div>
                </div>
                <div class="statement-page-break"></div>
            </section>
        </template>
        </div>
        <div class="statement-print-footer" data-page-label="<?= html_escape(lang('finance_statement_page_label')); ?>" data-of-label="<?= html_escape(lang('finance_statement_page_of_label')); ?>"></div>
    </div>
</div>

<script type="text/javascript">
(function() {
    'use strict';

    var apiUrl = "<?= $financeStatementApiUrl; ?>";
    var csvUrl = "<?= $financeStatementCsvUrl; ?>";
    var pdfUrl = "<?= $financeStatementPdfUrl; ?>";
    var form = document.getElementById('finance-statement-filter');
    var statusBox = document.getElementById('statement-status');
    var tableContainer = document.getElementById('statement-table-container');
    var rowCountEl = document.getElementById('statement-row-count');
    var csvButton = document.getElementById('statement-download-csv');
    var pdfButton = document.getElementById('statement-download-pdf');
    var summaryStudents = document.getElementById('summary-total-students');
    var summaryBalance = document.getElementById('summary-total-balance');
    var summaryDebit = document.getElementById('summary-total-debit');
    var summaryCredit = document.getElementById('summary-total-credit');
    var generatedAtEl = document.getElementById('statement-generated-at');
    var generatedByEl = document.getElementById('statement-generated-by');
    var footerPanel = document.getElementById('statement-footer');
    var footerText = document.getElementById('statement-footer-text');
    var currentQuery = '';
    var latestSummary = {};
    var latestFilters = {};
    var activeFetchController = null;
    var currentRequestToken = 0;
    var studentTemplate = document.getElementById('statement-student-template');
    var siteInfo = <?= json_encode([
        'name' => isset($siteinfos->sname) ? $siteinfos->sname : '',
        'address' => isset($siteinfos->address) ? $siteinfos->address : '',
        'phone' => isset($siteinfos->phone) ? $siteinfos->phone : '',
        'email' => isset($siteinfos->email) ? $siteinfos->email : '',
        'logo' => (isset($siteinfos->photo) && $siteinfos->photo) ? base_url('uploads/images/' . $siteinfos->photo) : '',
        'currencyCode' => $currencyCode,
        'currencySymbol' => $currencySymbol,
        'currencyLabel' => $currencyDisplay,
    ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE); ?>;

    var classSelect = document.getElementById('classesID');
    var sectionSelect = document.getElementById('sectionID');
    var studentSelect = document.getElementById('studentID');
    var parentSelect = document.getElementById('parentID');
    var termSelect = document.getElementById('schooltermID');
    var schoolYearSelectEl = document.getElementById('schoolYearID');

    var defaultClassOptions = classSelect ? classSelect.innerHTML : '';
    var defaultSectionOptions = sectionSelect ? sectionSelect.innerHTML : '';
    var defaultStudentOptions = studentSelect ? studentSelect.innerHTML : '';
    var defaultParentOptions = parentSelect ? parentSelect.innerHTML : '';
    var defaultTermOptions = termSelect ? termSelect.innerHTML : '';
    var defaultSchoolYearOptions = schoolYearSelectEl ? schoolYearSelectEl.innerHTML : '';

    var labels = <?= json_encode([
        'generatedAt' => lang('finance_statement_generated_at_label'),
        'generatedBy' => lang('finance_statement_generated_by_label'),
        'studentDetails' => lang('finance_statement_student_details'),
        'parentDetails' => lang('finance_statement_parent_details'),
        'noParentDetails' => lang('finance_statement_no_parent_details'),
        'admission' => lang('global_register_no'),
        'studentId' => lang('finance_statement_student_id_label'),
        'class' => $this->lang->line('global_classes'),
        'section' => $this->lang->line('global_section'),
        'group' => lang('finance_statement_group_label'),
        'role' => lang('finance_statement_role_label'),
        'range' => lang('finance_statement_range_label'),
        'rangeSeparator' => lang('finance_statement_range_separator'),
        'balance' => lang('finance_statement_table_running_balance'),
        'runningBalance' => lang('finance_statement_table_running_balance'),
        'periodBalance' => lang('finance_statement_summary_period_balance_column'),
        'debit' => lang('finance_statement_table_debit'),
        'credit' => lang('finance_statement_table_credit'),
        'date' => $this->lang->line('global_date'),
        'description' => $this->lang->line('global_description'),
        'subtotal' => lang('finance_statement_term_subtotal'),
        'activity' => lang('finance_statement_activity'),
        'parentEmail' => lang('global_email'),
        'parentPhone' => lang('global_phone_number'),
        'parentAddress' => lang('finance_statement_parent_address'),
        'parentName' => lang('finance_statement_parent_name'),
        'phone' => lang('global_phone_number'),
        'email' => lang('global_email'),
        'currency' => lang('finance_statement_currency_label'),
        'studentAddress' => lang('finance_statement_student_address'),
        'studentEmail' => lang('finance_statement_student_email'),
        'studentPhone' => lang('finance_statement_student_phone'),
        'termColumn' => lang('finance_statement_summary_term_column'),
        'academicYear' => lang('finance_statement_summary_year_column'),
        'summaryHeading' => lang('finance_statement_summary_heading'),
        'summaryOpening' => lang('finance_statement_opening_balance'),
        'summaryTermHeading' => lang('finance_statement_summary_term_heading'),
        'summaryYearHeading' => lang('finance_statement_summary_year_heading'),
        'summaryTotals' => lang('finance_statement_summary_totals'),
        'summaryClosing' => lang('finance_statement_summary_closing_balance'),
        'totals' => lang('finance_statement_statement_totals'),
        'invalidRange' => lang('finance_statement_invalid_range'),
        'noResults' => lang('finance_statement_no_results')
    ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE); ?>;

    function toNumber(value) {
        var number = Number(value);
        return Number.isFinite(number) ? number : 0;
    }

    function isTruthy(value) {
        if (value === null || value === undefined) {
            return false;
        }
        if (typeof value === 'boolean') {
            return value;
        }
        if (typeof value === 'number') {
            return value !== 0;
        }
        if (typeof value === 'string') {
            var lowered = value.toLowerCase();
            return lowered !== '0' && lowered !== 'false' && lowered !== '';
        }
        return !!value;
    }

    var cachedCurrencyFormatter;
    var cachedIntegerFormatter;

    function getCurrencyFormatter() {
        if (cachedCurrencyFormatter) {
            return cachedCurrencyFormatter;
        }
        if (typeof Intl !== 'undefined' && typeof Intl.NumberFormat === 'function') {
            try {
                cachedCurrencyFormatter = new Intl.NumberFormat(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            } catch (e) {
                cachedCurrencyFormatter = null;
            }
        }
        return cachedCurrencyFormatter;
    }

    function getIntegerFormatter() {
        if (cachedIntegerFormatter) {
            return cachedIntegerFormatter;
        }
        if (typeof Intl !== 'undefined' && typeof Intl.NumberFormat === 'function') {
            try {
                cachedIntegerFormatter = new Intl.NumberFormat(undefined, { maximumFractionDigits: 0 });
            } catch (e) {
                cachedIntegerFormatter = null;
            }
        }
        return cachedIntegerFormatter;
    }

    function formatCurrency(value) {
        var number = toNumber(value);
        var formatter = getCurrencyFormatter();
        if (formatter) {
            return formatter.format(number);
        }
        return number.toFixed(2);
    }

    function formatInteger(value) {
        var number = toNumber(value);
        var formatter = getIntegerFormatter();
        if (formatter) {
            return formatter.format(number);
        }
        return Math.round(number);
    }

    function escapeHtml(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function formatDate(value) {
        if (!value) {
            return '';
        }
        var date = new Date(value);
        if (!isNaN(date.getTime())) {
            return date.toLocaleDateString();
        }
        return value;
    }

    function formatDateTime(value) {
        if (!value) {
            return '';
        }
        var date = new Date(value);
        if (!isNaN(date.getTime())) {
            return date.toLocaleString();
        }
        return value;
    }

    function formatRange(range) {
        if (!range) {
            return '';
        }
        var from = formatDate(range.from);
        var to = formatDate(range.to);
        if (from && to) {
            return from + ' ' + labels.rangeSeparator + ' ' + to;
        }
        return from || to;
    }

    function setButtonsEnabled(enabled) {
        csvButton.disabled = !enabled;
        pdfButton.disabled = !enabled;
        if (!enabled) {
            currentQuery = '';
        }
    }

    function updateStatus(message, type) {
        if (!message) {
            statusBox.style.display = 'none';
            return;
        }
        statusBox.className = 'alert alert-' + type;
        statusBox.textContent = message;
        statusBox.style.display = 'block';
    }

    function updateGeneratedMeta(summary) {
        summary = summary || {};
        var generatedAt = summary.generated_at || '';
        var generatedBy = summary.generated_by || '';
        var formattedDate = formatDateTime(generatedAt);

        if (generatedAtEl) {
            generatedAtEl.textContent = generatedAt ? labels.generatedAt + ': ' + formattedDate : '';
        }

        if (generatedByEl) {
            generatedByEl.textContent = generatedBy ? labels.generatedBy + ': ' + generatedBy : '';
        }

        if (footerPanel && footerText) {
            var pieces = [];
            if (generatedBy) {
                pieces.push(labels.generatedBy + ': ' + generatedBy);
            }
            if (generatedAt) {
                pieces.push(labels.generatedAt + ': ' + formattedDate);
            }
            footerText.textContent = pieces.join(' | ');
            footerPanel.style.display = pieces.length ? 'block' : 'none';
        }
    }

    function updateSummary(summary) {
        summary = summary || {};
        latestSummary = summary;
        summaryStudents.textContent = formatInteger(summary.total_students || 0);
        summaryBalance.textContent = formatCurrency(summary.total_balance || 0);
        summaryDebit.textContent = formatCurrency(summary.total_debit || 0);
        summaryCredit.textContent = formatCurrency(summary.total_credit || 0);
        updateGeneratedMeta(summary);
    }

    function renderTermTable(term) {
        var table = document.createElement('table');
        table.className = 'table table-striped table-bordered';

        var thead = document.createElement('thead');
        thead.innerHTML = '<tr>' +
            '<th>' + escapeHtml(labels.date) + '</th>' +
            '<th>' + escapeHtml(labels.description) + '</th>' +
            '<th class="text-right">' + escapeHtml(labels.debit) + '</th>' +
            '<th class="text-right">' + escapeHtml(labels.credit) + '</th>' +
            '<th class="text-right">' + escapeHtml(labels.runningBalance) + '</th>' +
            '</tr>';
        table.appendChild(thead);

        var tbody = document.createElement('tbody');
        var rows = Array.isArray(term.rows) ? term.rows : [];
        var hasSummaryRow = false;
        var isOpeningGroup = !!term.is_opening;
        var hasTransactions = term.has_transactions !== false;
        rows.forEach(function(row) {
            var type = row.type || '';
            var tr = document.createElement('tr');
            if (type === 'term_summary') {
                hasSummaryRow = true;
                tr.className = 'statement-term-subtotal';
                var summaryLabel = labels.subtotal;
                if (row.term_label) {
                    summaryLabel += ' - ' + row.term_label;
                }
                tr.innerHTML = '<td colspan="2" class="text-right">' + escapeHtml(summaryLabel) + '</td>' +
                    '<td class="text-right">' + formatCurrency(row.debit || 0) + '</td>' +
                    '<td class="text-right">' + formatCurrency(row.credit || 0) + '</td>' +
                    '<td class="text-right">' + formatCurrency(row.running_balance || row.balance || 0) + '</td>';
            } else {
                var debit = row.debit ? formatCurrency(row.debit) : '';
                var credit = row.credit ? formatCurrency(row.credit) : '';
                tr.innerHTML = '<td>' + escapeHtml(row.day || '') + '</td>' +
                    '<td>' + escapeHtml(row.description || '') + '</td>' +
                    '<td class="text-right">' + debit + '</td>' +
                    '<td class="text-right">' + credit + '</td>' +
                    '<td class="text-right">' + formatCurrency(row.running_balance != null ? row.running_balance : row.balance) + '</td>';
            }
            tbody.appendChild(tr);
        });

        var shouldAddSubtotal = !hasSummaryRow && rows.length && !isOpeningGroup && hasTransactions;
        if (shouldAddSubtotal) {
            var subtotal = term.subtotal || {};
            var summaryRow = document.createElement('tr');
            summaryRow.className = 'statement-term-subtotal';
            summaryRow.innerHTML = '<td colspan="2" class="text-right">' + escapeHtml(labels.subtotal) + '</td>' +
                '<td class="text-right">' + formatCurrency(subtotal.debit || 0) + '</td>' +
                '<td class="text-right">' + formatCurrency(subtotal.credit || 0) + '</td>' +
                '<td class="text-right">' + formatCurrency(subtotal.balance || 0) + '</td>';
            tbody.appendChild(summaryRow);
        }

        if (!rows.length) {
            var emptyRow = document.createElement('tr');
            emptyRow.innerHTML = '<td colspan="5" class="text-center text-muted">' + escapeHtml(labels.noResults) + '</td>';
            tbody.appendChild(emptyRow);
        }

        table.appendChild(tbody);
        return table;
    }

    function buildStudentBlock(student) {
        var parts = [];
        parts.push('<strong>' + escapeHtml(student.student.student_name || '') + '</strong>');
        if (student.student.admission_number) {
            parts.push(escapeHtml(labels.admission) + ': ' + escapeHtml(student.student.admission_number));
        }
        if (student.student.studentID) {
            parts.push(escapeHtml(labels.studentId) + ': ' + escapeHtml(student.student.studentID));
        }
        if (student.student.class) {
            parts.push(escapeHtml(labels.class) + ': ' + escapeHtml(student.student.class));
        }
        if (student.student.section) {
            parts.push(escapeHtml(labels.section) + ': ' + escapeHtml(student.student.section));
        }
        if (student.student.group) {
            parts.push(escapeHtml(labels.group) + ': ' + escapeHtml(student.student.group));
        }
        if (student.student.role) {
            parts.push(escapeHtml(labels.role) + ': ' + escapeHtml(student.student.role));
        }
        if (student.student.address) {
            parts.push(escapeHtml(labels.studentAddress) + ': ' + escapeHtml(student.student.address));
        }
        if (student.student.email) {
            parts.push(escapeHtml(labels.studentEmail) + ': ' + escapeHtml(student.student.email));
        }
        if (student.student.phone) {
            parts.push(escapeHtml(labels.studentPhone) + ': ' + escapeHtml(student.student.phone));
        }
        var rangeText = formatRange(student.range);
        if (rangeText) {
            parts.push(escapeHtml(labels.range) + ': ' + escapeHtml(rangeText));
        }
        return parts.join('<br>');
    }

    function buildParentBlock(student) {
        var parent = student.parent || {};
        var includeParent = !('includeParentDetails' in latestFilters) || isTruthy(latestFilters.includeParentDetails);

        var pieces = ['<strong>' + escapeHtml(labels.totals) + '</strong>'];
        pieces.push(escapeHtml(labels.debit) + ': ' + formatCurrency(student.student.total_debit));
        pieces.push(escapeHtml(labels.credit) + ': ' + formatCurrency(student.student.total_credit));
        pieces.push('<strong>' + escapeHtml(labels.runningBalance) + ': ' + formatCurrency(student.student.closing_balance) + '</strong>');

        if (!includeParent) {
            return pieces.join('<br>');
        }

        var detailLines = [];
        if (parent.name) {
            detailLines.push(escapeHtml(labels.parentName) + ': ' + escapeHtml(parent.name));
        }
        if (parent.email) {
            detailLines.push(escapeHtml(labels.parentEmail) + ': ' + escapeHtml(parent.email));
        }
        if (parent.phone) {
            detailLines.push(escapeHtml(labels.parentPhone) + ': ' + escapeHtml(parent.phone));
        }
        if (parent.address) {
            detailLines.push(escapeHtml(labels.parentAddress) + ': ' + escapeHtml(parent.address));
        }

        pieces.push('');
        pieces.push('<strong>' + escapeHtml(labels.parentDetails) + '</strong>');
        if (detailLines.length) {
            pieces = pieces.concat(detailLines);
        } else {
            pieces.push('<span class="text-muted">' + escapeHtml(labels.noParentDetails) + '</span>');
        }

        return pieces.join('<br>');
    }

    function buildSummaryBlock(student) {
        var summary = student.balance_summary || {};
        if (!summary || typeof summary !== 'object') {
            return null;
        }

        var container = document.createElement('div');
        container.className = 'statement-summary-block';

        var heading = document.createElement('h4');
        heading.className = 'statement-summary-heading';
        heading.textContent = labels.summaryHeading;
        container.appendChild(heading);

        var table = document.createElement('table');
        table.className = 'table table-striped table-bordered statement-summary-table';

        var thead = document.createElement('thead');
        thead.innerHTML = '<tr>' +
            '<th>' + escapeHtml(labels.description) + '</th>' +
            '<th class="text-right">' + escapeHtml(labels.debit) + '</th>' +
            '<th class="text-right">' + escapeHtml(labels.credit) + '</th>' +
            '<th class="text-right">' + escapeHtml(labels.periodBalance) + '</th>' +
            '<th class="text-right">' + escapeHtml(labels.runningBalance) + '</th>' +
            '<th>' + escapeHtml(labels.termColumn) + '</th>' +
            '<th>' + escapeHtml(labels.academicYear) + '</th>' +
            '</tr>';
        table.appendChild(thead);

        var tbody = document.createElement('tbody');

        function appendRow(description, debit, credit, periodBalance, runningBalance, term, year, type) {
            var tr = document.createElement('tr');
            if (type) {
                tr.setAttribute('data-summary-type', type);
            }
            tr.innerHTML = '<td>' + escapeHtml(description) + '</td>' +
                '<td class="text-right">' + (debit !== '' ? formatCurrency(debit) : '') + '</td>' +
                '<td class="text-right">' + (credit !== '' ? formatCurrency(credit) : '') + '</td>' +
                '<td class="text-right">' + (periodBalance !== '' ? formatCurrency(periodBalance) : '') + '</td>' +
                '<td class="text-right">' + (runningBalance !== '' ? formatCurrency(runningBalance) : '') + '</td>' +
                '<td>' + escapeHtml(term || '') + '</td>' +
                '<td>' + escapeHtml(year || '') + '</td>';
            tbody.appendChild(tr);
        }

        appendRow(labels.summaryOpening, '', '', '', summary.opening_balance || 0, '', '', 'opening');

        (summary.terms || []).forEach(function(term) {
            if (!term) {
                return;
            }
            var label = term.label || '';
            var periodBalance = term.period_balance != null ? term.period_balance : (toNumber(term.debit) - toNumber(term.credit));
            appendRow(
                labels.summaryTermHeading + ': ' + label,
                term.debit || 0,
                term.credit || 0,
                periodBalance,
                term.balance || 0,
                label,
                term.schoolyear_label || '',
                'term'
            );
        });

        (summary.academic_years || []).forEach(function(year) {
            if (!year) {
                return;
            }
            var label = year.label || '';
            var yearPeriodBalance = year.period_balance != null ? year.period_balance : (toNumber(year.debit) - toNumber(year.credit));
            appendRow(
                labels.summaryYearHeading + ': ' + label,
                year.debit || 0,
                year.credit || 0,
                yearPeriodBalance,
                year.balance || 0,
                '',
                label,
                'year'
            );
        });

        var totalsPeriodBalance = (summary.total_debit || 0) - (summary.total_credit || 0);
        appendRow(labels.summaryTotals, summary.total_debit || 0, summary.total_credit || 0, totalsPeriodBalance, '', '', '', 'totals');
        appendRow(labels.summaryClosing, '', '', '', summary.closing_balance || 0, '', '', 'closing');

        table.appendChild(tbody);
        container.appendChild(table);

        return container;
    }

    function renderStudentCard(student, summary) {
        summary = summary || latestSummary || {};

        var fragment;
        var invoice;
        if (studentTemplate && 'content' in studentTemplate) {
            fragment = studentTemplate.content.cloneNode(true);
            invoice = fragment.querySelector('.statement-invoice');
        } else if (studentTemplate) {
            invoice = studentTemplate.cloneNode(true);
            invoice.removeAttribute('id');
            fragment = document.createDocumentFragment();
            fragment.appendChild(invoice);
        } else {
            invoice = document.createElement('section');
            invoice.className = 'statement-invoice content invoice';
            fragment = document.createDocumentFragment();
            fragment.appendChild(invoice);
        }

        if (!invoice) {
            return fragment || document.createDocumentFragment();
        }

        var logoEl = invoice.querySelector('.statement-invoice-logo');
        if (logoEl) {
            if (siteInfo.logo) {
                logoEl.src = siteInfo.logo;
                logoEl.alt = siteInfo.name || '';
                logoEl.style.display = 'inline-block';
            } else {
                logoEl.parentNode.removeChild(logoEl);
            }
        }

        var schoolNameEl = invoice.querySelector('[data-field="school-name"]');
        if (schoolNameEl) {
            schoolNameEl.textContent = siteInfo.name || '';
        }

        var generatedAtDisplay = summary.generated_at ? labels.generatedAt + ': ' + formatDateTime(summary.generated_at) : '';
        var headerGeneratedEl = invoice.querySelector('[data-field="generated-at"]');
        if (headerGeneratedEl) {
            headerGeneratedEl.textContent = generatedAtDisplay;
        }

        var studentBlock = invoice.querySelector('[data-field="student-block"]');
        if (studentBlock) {
            studentBlock.innerHTML = buildStudentBlock(student);
        }

        var parentBlock = invoice.querySelector('[data-field="parent-block"]');
        if (parentBlock) {
            parentBlock.innerHTML = buildParentBlock(student);
        }

        var termsContainer = invoice.querySelector('.statement-terms');
        if (termsContainer) {
            termsContainer.innerHTML = '';
            var terms = Array.isArray(student.terms) ? student.terms : [];
            if ((!terms || !terms.length) && student.rows && student.rows.length) {
                terms = [{
                    label: labels.activity,
                    rows: student.rows.slice(),
                    subtotal: {
                        debit: student.student.total_debit,
                        credit: student.student.total_credit,
                        balance: student.student.closing_balance
                    },
                    is_opening: false,
                    has_transactions: true
                }];
                terms[0].rows.push({
                    type: 'term_summary',
                    term: labels.activity,
                    term_label: labels.activity,
                    description: labels.subtotal,
                    debit: student.student.total_debit,
                    credit: student.student.total_credit,
                    balance: student.student.closing_balance,
                    running_balance: student.student.closing_balance,
                    day: null
                });
            }

            terms.forEach(function(term) {
                if (!term) {
                    return;
                }
                var heading = document.createElement('h4');
                heading.textContent = term.label || labels.activity;
                termsContainer.appendChild(heading);
                termsContainer.appendChild(renderTermTable(term));
            });
        }

        var summaryContainer = invoice.querySelector('.statement-summary-container');
        if (summaryContainer) {
            summaryContainer.innerHTML = '';
            var summaryBlock = buildSummaryBlock(student);
            if (summaryBlock) {
                summaryContainer.appendChild(summaryBlock);
            }
        }

        var footerMetaEl = invoice.querySelector('[data-field="footer-meta"]');
        if (footerMetaEl) {
            var footerParts = [];
            if (summary.generated_by) {
                footerParts.push(labels.generatedBy + ': ' + summary.generated_by);
            }
            if (summary.generated_at) {
                footerParts.push(labels.generatedAt + ': ' + formatDateTime(summary.generated_at));
            }
            footerMetaEl.textContent = footerParts.join(' | ');
        }

        return fragment;
    }

    function renderStatements(payload, summary) {
        tableContainer.innerHTML = '';
        summary = summary || {};
        latestFilters = (payload && payload.filters) ? payload.filters : {};
        var students = (payload && payload.students) ? payload.students : [];
        if (!students.length) {
            rowCountEl.textContent = '<?= $this->lang->line('global_total'); ?>: 0';
            updateStatus('<?= $this->lang->line('finance_statement_no_results'); ?>', 'warning');
            updateSummary({});
            setButtonsEnabled(false);
            return;
        }

        updateStatus('', 'info');
        var totalRows = payload.total_rows || 0;
        rowCountEl.textContent = '<?= $this->lang->line('global_total'); ?>: ' + formatInteger(totalRows);
        setButtonsEnabled(true);
        updateSummary(summary);

        var fragment = document.createDocumentFragment();
        students.forEach(function(student) {
            fragment.appendChild(renderStudentCard(student, summary));
        });

        tableContainer.appendChild(fragment);
    }

    function fetchStatements() {
        var dateFromInput = document.getElementById('dateFrom');
        var dateToInput = document.getElementById('dateTo');
        var dateFromValue = dateFromInput ? dateFromInput.value : '';
        var dateToValue = dateToInput ? dateToInput.value : '';

        if (dateFromValue && dateToValue) {
            var fromDate = new Date(dateFromValue);
            var toDate = new Date(dateToValue);
            if (fromDate > toDate) {
                setButtonsEnabled(false);
                updateStatus(labels.invalidRange, 'warning');
                return;
            }
        }

        updateStatus('<?= $this->lang->line('global_loading'); ?>...', 'info');
        setButtonsEnabled(false);
        tableContainer.innerHTML = '';
        rowCountEl.textContent = '';
        updateSummary({});

        var formData = new FormData(form);
        var parentCheckbox = document.getElementById('includeParentDetails');
        var studentCheckbox = document.getElementById('includeStudentDetails');
        if (parentCheckbox) {
            formData.set('includeParentDetails', parentCheckbox.checked ? '1' : '0');
        }
        if (studentCheckbox) {
            formData.set('includeStudentDetails', studentCheckbox.checked ? '1' : '0');
        }

        var params = new URLSearchParams(formData);
        currentQuery = params.toString();
        var requestToken = ++currentRequestToken;

        if (activeFetchController && typeof activeFetchController.abort === 'function') {
            activeFetchController.abort();
        }

        if (typeof AbortController !== 'undefined') {
            activeFetchController = new AbortController();
        } else {
            activeFetchController = null;
        }

        var fetchOptions = { credentials: 'same-origin' };
        if (activeFetchController && activeFetchController.signal) {
            fetchOptions.signal = activeFetchController.signal;
        }

        fetch(apiUrl + '?' + currentQuery, fetchOptions).then(function(response) {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        }).then(function(json) {
            activeFetchController = null;
            if (requestToken !== currentRequestToken) {
                return;
            }
            if (json && json.data) {
                renderStatements(json.data, json.summary || {});
            } else {
                updateStatus('<?= $this->lang->line('finance_statement_no_results'); ?>', 'warning');
                updateSummary({});
            }
        }).catch(function(error) {
            if (error && error.name === 'AbortError') {
                activeFetchController = null;
                return;
            }
            if (requestToken !== currentRequestToken) {
                return;
            }
            console.error(error);
            updateStatus('<?= $this->lang->line('global_error'); ?>', 'danger');
            updateSummary({});
        });
    }

    csvButton.addEventListener('click', function() {
        if (!currentQuery) return;
        window.location = csvUrl + '?' + currentQuery;
    });

    pdfButton.addEventListener('click', function() {
        if (!currentQuery) return;
        window.open(pdfUrl + '?' + currentQuery, '_blank', 'noopener');
    });

    form.addEventListener('submit', function(event) {
        event.preventDefault();
        fetchStatements();
    });

    var $ = window.jQuery || window.$;
    if ($) {
        var $classesSelect = $('#classesID');
        var $sectionSelect = $('#sectionID');
        var $studentSelect = $('#studentID');
        var $parentSelect = $('#parentID');
        var $termSelect = $('#schooltermID');
        var $roleSelect = $('#roleID');
        var $schoolYearSelect = $('#schoolYearID');
        var $schoolYearWrapper = $('#filter-schoolyear-wrapper');
        var $termWrapper = $('#filter-term-wrapper');
        var $studentRow = $('#student-filter-row');
        var $parentRow = $('#parent-filter-row');

        $('.select2').select2();

        function resetSelect($element, defaultHtml) {
            if (!$element || !$element.length) {
                return;
            }
            if (typeof defaultHtml === 'string' && defaultHtml.length) {
                $element.html(defaultHtml);
            }
            $element.val('0').trigger('change.select2');
        }

        function toggleGroup($element, shouldShow) {
            if (!$element || !$element.length) {
                return;
            }
            if (shouldShow) {
                $element.show();
            } else {
                $element.hide();
            }
        }

        function updateRoleFilters(role) {
            role = role || '0';
            var isStudent = role === '3';
            var isParent = role === '4';

            toggleGroup($schoolYearWrapper, isStudent);
            toggleGroup($termWrapper, isStudent);
            toggleGroup($studentRow, isStudent);
            toggleGroup($parentRow, isParent);

            if (!isStudent) {
                resetSelect($schoolYearSelect, defaultSchoolYearOptions);
                resetSelect($classesSelect, defaultClassOptions);
                resetSelect($sectionSelect, defaultSectionOptions);
                resetSelect($studentSelect, defaultStudentOptions);
                resetSelect($termSelect, defaultTermOptions);
            }

            if (!isParent) {
                resetSelect($parentSelect, defaultParentOptions);
            }
        }

        $classesSelect.on('change', function() {
            var id = $(this).val() || '0';
            if (id === '0') {
                resetSelect($sectionSelect, defaultSectionOptions);
                resetSelect($studentSelect, defaultStudentOptions);
                resetSelect($parentSelect, defaultParentOptions);
                return;
            }

            $.ajax({
                type: 'POST',
                url: "<?= base_url('finance_statement/sectioncall'); ?>",
                data: {"id" : id},
                dataType: "html",
                success: function(data) {
                    $sectionSelect.html(data).val('0').trigger('change.select2');
                }
            });

            $.ajax({
                type: 'POST',
                url: "<?= base_url('finance_statement/studentcall'); ?>",
                data: {"classesID" : id},
                dataType: "html",
                success: function(data) {
                    $studentSelect.html(data).val('0').trigger('change.select2');
                }
            });

            $.ajax({
                type: 'POST',
                url: "<?= base_url('finance_statement/parentcall'); ?>",
                data: {"classesID" : id},
                dataType: "html",
                success: function(data) {
                    $parentSelect.html(data).val('0').trigger('change.select2');
                }
            });
        });

        $sectionSelect.on('change', function() {
            var id = $(this).val() || '0';
            var classesID = $classesSelect.val() || '0';
            if (id === '0') {
                resetSelect($studentSelect, defaultStudentOptions);
                resetSelect($parentSelect, defaultParentOptions);
                return;
            }

            if (classesID === '0') {
                resetSelect($classesSelect, defaultClassOptions);
                return;
            }

            $.ajax({
                type: 'POST',
                url: "<?= base_url('finance_statement/studentcall'); ?>",
                data: {"classesID" : classesID, "sectionID" : id},
                dataType: "html",
                success: function(data) {
                    $studentSelect.html(data).val('0').trigger('change.select2');
                }
            });

            $.ajax({
                type: 'POST',
                url: "<?= base_url('finance_statement/parentcall'); ?>",
                data: {"classesID" : classesID, "sectionID" : id},
                dataType: "html",
                success: function(data) {
                    $parentSelect.html(data).val('0').trigger('change.select2');
                }
            });
        });

        $schoolYearSelect.on('change', function() {
            var id = $(this).val() || '0';
            if (id === '0') {
                resetSelect($termSelect, defaultTermOptions);
                return;
            }

            $.ajax({
                type: 'POST',
                url: "<?= base_url('finance_statement/termcall'); ?>",
                data: {"schoolYearID" : id},
                dataType: "html",
                success: function(data) {
                    $termSelect.html(data).val('0').trigger('change.select2');
                }
            });
        });

        $termSelect.on('change', function() {
            var id = $(this).val() || '0';
            if (id === '0') {
                $('#dateFrom').val('');
                $('#dateTo').val('');
                return;
            }

            $.ajax({
                type: 'POST',
                url: "<?= base_url('finance_statement/datescall'); ?>",
                data: {"schooltermID" : id},
                dataType: "json",
                success: function(data) {
                    $('#dateFrom').val(data.startingdate);
                    $('#dateTo').val(data.endingdate);
                }
            });
        });

        $roleSelect.on('change', function() {
            updateRoleFilters($(this).val() || '0');
        });

        updateRoleFilters($roleSelect.val() || '0');
    }
})();

</script>
