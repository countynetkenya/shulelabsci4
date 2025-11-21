<?php
$title = lang('menu_payroll');
if ($title === 'menu_payroll') {
    $title = 'Payroll';
}

$filtersLabel = lang('payroll_filters');
if ($filtersLabel === 'payroll_filters') {
    $filtersLabel = 'Filters';
}

$summary = isset($payrollSummary) ? $payrollSummary : ['total_employees' => 0, 'paid_employees' => 0, 'outstanding_employees' => 0, 'total_payout' => 0, 'average_net' => 0];
$rows = isset($payrollRows) ? $payrollRows : [];
$roleBreakdown = isset($roleBreakdown) ? $roleBreakdown : [];
$selectedRole = isset($selectedRole) ? (int) $selectedRole : 0;
$selectedMonth = isset($selectedMonth) ? $selectedMonth : date('Y-m');
$roleLookup = [];
if (!empty($roles)) {
    foreach ($roles as $role) {
        $roleLookup[$role->usertypeID] = $role->usertype;
    }
}
?>

<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><i class="fa fa-credit-card"></i> <?= html_escape($title); ?></h3>
        <div class="box-tools pull-right">
            <a href="<?= html_escape($payrollCsvUrl); ?>" class="btn btn-default btn-sm">
                <i class="fa fa-download"></i> <?= html_escape(lang('payroll_download_csv')); ?>
            </a>
        </div>
    </div>
    <div class="box-body">
        <form method="get" class="form-inline m-b-20">
            <div class="form-group m-r-20">
                <label for="role" class="control-label m-r-5"><?= html_escape(lang('payroll_filter_role')); ?></label>
                <select name="role" id="role" class="form-control select2" style="min-width:180px">
                    <option value="0"<?= $selectedRole === 0 ? ' selected' : ''; ?>><?= html_escape(lang('payroll_all_roles')); ?></option>
                    <?php if (!empty($roles)): ?>
                        <?php foreach ($roles as $role): ?>
                            <option value="<?= (int) $role->usertypeID; ?>"<?= $selectedRole === (int) $role->usertypeID ? ' selected' : ''; ?>><?= html_escape($role->usertype); ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="form-group m-r-20">
                <label for="month" class="control-label m-r-5"><?= html_escape(lang('payroll_filter_month')); ?></label>
                <input type="month" class="form-control" name="month" id="month" value="<?= html_escape($selectedMonth); ?>">
            </div>
            <button type="submit" class="btn btn-primary"><?= html_escape(lang('payroll_apply_filters')); ?></button>
        </form>

        <div class="row m-b-20">
            <div class="col-sm-3">
                <div class="small-box bg-aqua">
                    <div class="inner">
                        <h3><?= (int) $summary['total_employees']; ?></h3>
                        <p><?= html_escape(lang('payroll_total_employees')); ?></p>
                    </div>
                    <div class="icon"><i class="fa fa-users"></i></div>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="small-box bg-green">
                    <div class="inner">
                        <h3><?= (int) $summary['paid_employees']; ?></h3>
                        <p><?= html_escape(lang('payroll_total_processed')); ?></p>
                    </div>
                    <div class="icon"><i class="fa fa-check"></i></div>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="small-box bg-yellow">
                    <div class="inner">
                        <h3><?= (int) $summary['outstanding_employees']; ?></h3>
                        <p><?= html_escape(lang('payroll_total_outstanding')); ?></p>
                    </div>
                    <div class="icon"><i class="fa fa-warning"></i></div>
                </div>
            </div>
            <div class="col-sm-3">
                <div class="small-box bg-purple">
                    <div class="inner">
                        <h3><?= number_format((float) $summary['total_payout'], 2); ?></h3>
                        <p><?= html_escape(lang('payroll_total_payout')); ?></p>
                    </div>
                    <div class="icon"><i class="fa fa-money"></i></div>
                </div>
            </div>
        </div>

        <div class="row m-b-20">
            <div class="col-sm-6">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title"><i class="fa fa-line-chart"></i> <?= html_escape(lang('payroll_summary_average_net')); ?></h3>
                    </div>
                    <div class="panel-body">
                        <strong><?= number_format((float) $summary['average_net'], 2); ?></strong>
                        <p class="text-muted m-b-0"><?= html_escape(lang('payroll_summary_average_net_help')); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-sm-6">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title"><i class="fa fa-pie-chart"></i> <?= html_escape(lang('payroll_role_breakdown')); ?></h3>
                    </div>
                    <div class="panel-body">
                        <?php if (!empty($roleBreakdown)): ?>
                            <ul class="list-unstyled">
                                <?php foreach ($roleBreakdown as $roleID => $count): ?>
                                    <li><?= html_escape(isset($roleLookup[$roleID]) ? $roleLookup[$roleID] : ('#' . $roleID)); ?> <span class="text-muted">&middot; <?= (int) $count; ?></span></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p class="text-muted m-b-0"><?= html_escape(lang('payroll_no_records')); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($rows)): ?>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th><?= html_escape(lang('payroll_table_employee')); ?></th>
                            <th><?= html_escape(lang('payroll_table_role')); ?></th>
                            <th><?= html_escape(lang('payroll_table_type')); ?></th>
                            <th><?= html_escape(lang('payroll_table_base')); ?></th>
                            <th><?= html_escape(lang('payroll_table_allowances')); ?></th>
                            <th><?= html_escape(lang('payroll_table_deductions')); ?></th>
                            <th><?= html_escape(lang('payroll_table_net')); ?></th>
                            <th><?= html_escape(lang('payroll_table_paid')); ?></th>
                            <th><?= html_escape(lang('payroll_table_last_paid')); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $row): ?>
                            <?php
                                $allowanceTotal = array_sum(array_map(static function ($allowance) {
                                    return (float) ($allowance['amount'] ?? 0.0);
                                }, $row['allowances']));
                                $deductionTotal = array_sum(array_map(static function ($deduction) {
                                    return (float) ($deduction['amount'] ?? 0.0);
                                }, $row['deductions']));
                            ?>
                            <tr>
                                <td>
                                    <strong><?= html_escape($row['user']['name']); ?></strong><br>
                                    <small><?= html_escape($row['user']['email']); ?></small><br>
                                    <?php if (!empty($row['user']['join_date'])): ?>
                                        <small><?= html_escape(lang('payroll_join_date')); ?>: <?= html_escape(date('M d, Y', strtotime($row['user']['join_date']))); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?= html_escape($row['role_name']); ?></td>
                                <td>
                                    <span class="label label-default text-uppercase"><?= html_escape(lang('payroll_salary_' . $row['salary_type'])); ?></span>
                                    <?php if (!empty($row['template'])): ?><br><small><?= html_escape($row['template']); ?></small><?php endif; ?>
                                </td>
                                <td class="text-right"><?= number_format((float) $row['base_salary'], 2); ?></td>
                                <td>
                                    <strong><?= number_format($allowanceTotal, 2); ?></strong>
                                    <?php if (!empty($row['allowances'])): ?>
                                        <details class="m-t-5"><summary><?= html_escape(lang('payroll_view_breakdown')); ?></summary>
                                            <ul class="list-unstyled">
                                                <?php foreach ($row['allowances'] as $allowance): ?>
                                                    <li><?= html_escape($allowance['label']); ?>: <?= number_format((float) $allowance['amount'], 2); ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </details>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?= number_format($deductionTotal, 2); ?></strong>
                                    <?php if (!empty($row['deductions'])): ?>
                                        <details class="m-t-5"><summary><?= html_escape(lang('payroll_view_breakdown')); ?></summary>
                                            <ul class="list-unstyled">
                                                <?php foreach ($row['deductions'] as $deduction): ?>
                                                    <li><?= html_escape($deduction['label']); ?>: <?= number_format((float) $deduction['amount'], 2); ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </details>
                                    <?php endif; ?>
                                </td>
                                <td class="text-right"><?= number_format((float) $row['summary']['net'], 2); ?></td>
                                <td class="text-right"><?= number_format((float) $row['paid_this_month'], 2); ?></td>
                                <td><?= $row['last_paid_at'] ? html_escape(date('M d, Y', strtotime($row['last_paid_at']))) : '<span class="text-muted">' . html_escape(lang('payroll_not_paid')) . '</span>'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fa fa-info-circle"></i> <?= html_escape(lang('payroll_no_records')); ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<script type="text/javascript">
    $(function() {
        $('.select2').select2({
            width: 'resolve'
        });
    });
</script>
