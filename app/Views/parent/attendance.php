<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <h1 class="page-header">
        <i class="fa fa-calendar-check"></i> Attendance - <?= esc($child['full_name'] ?? 'Student') ?>
        <a href="/parent/children" class="btn btn-default pull-right">
            <i class="fa fa-arrow-left"></i> Back to Children
        </a>
    </h1>

    <div class="row">
        <div class="col-lg-3 col-md-6">
            <div class="panel panel-success">
                <div class="panel-body text-center">
                    <h2><?= $attendance_summary['attendance_rate'] ?? 0 ?>%</h2>
                    <p>Attendance Rate</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="panel panel-primary">
                <div class="panel-body text-center">
                    <h2><?= $attendance_summary['present_days'] ?? 0 ?></h2>
                    <p>Present Days</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="panel panel-danger">
                <div class="panel-body text-center">
                    <h2><?= $attendance_summary['absent_days'] ?? 0 ?></h2>
                    <p>Absent Days</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="panel panel-info">
                <div class="panel-body text-center">
                    <h2><?= $attendance_summary['total_days'] ?? 0 ?></h2>
                    <p>Total Days</p>
                </div>
            </div>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading"><i class="fa fa-list"></i> Attendance Records (Last 30 Days)</div>
        <div class="panel-body">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($attendance_records) && !empty($attendance_records)): ?>
                        <?php foreach ($attendance_records as $record): ?>
                            <tr>
                                <td><?= esc($record['date']) ?></td>
                                <td>
                                    <?php if ($record['status'] === 'present'): ?>
                                        <span class="label label-success">Present</span>
                                    <?php else: ?>
                                        <span class="label label-danger">Absent</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= esc($record['remarks'] ?? '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="3" class="text-center">No attendance records available</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
