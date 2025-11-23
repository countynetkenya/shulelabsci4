<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <h1 class="page-header"><i class="fa fa-bar-chart-o"></i> Reports & Analytics</h1>

    <div class="row">
        <div class="col-lg-3 col-md-6">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-3"><i class="fa fa-users fa-5x"></i></div>
                        <div class="col-xs-9 text-right">
                            <div class="huge"><?= $report_data['total_enrollments'] ?? 0 ?></div>
                            <div>Total Enrollments</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="panel panel-default">
                <div class="panel-heading"><i class="fa fa-table"></i> Enrollment by Class</div>
                <div class="panel-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Class</th>
                                <th>Students</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($report_data['enrollment_by_class']) && !empty($report_data['enrollment_by_class'])): ?>
                                <?php foreach ($report_data['enrollment_by_class'] as $class): ?>
                                    <tr>
                                        <td><?= esc($class['class_name']) ?></td>
                                        <td><?= esc($class['student_count']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="2" class="text-center">No data available</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="panel panel-default">
                <div class="panel-heading"><i class="fa fa-trophy"></i> Recent Grades</div>
                <div class="panel-body">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Grade</th>
                                <th>Marks</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (isset($report_data['recent_grades']) && !empty($report_data['recent_grades'])): ?>
                                <?php foreach ($report_data['recent_grades'] as $grade): ?>
                                    <tr>
                                        <td>Student #<?= esc($grade['student_id']) ?></td>
                                        <td><span class="label label-success"><?= esc($grade['grade'] ?? 'N/A') ?></span></td>
                                        <td><?= esc($grade['marks_obtained'] ?? 0) ?>/<?= esc($grade['total_marks'] ?? 100) ?></td>
                                        <td><?= esc($grade['created_at'] ?? 'N/A') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="text-center">No grades recorded yet</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
