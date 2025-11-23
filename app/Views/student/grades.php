<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <h1 class="page-header"><i class="fa fa-trophy"></i> My Grades</h1>

    <div class="row">
        <div class="col-lg-3 col-md-6">
            <div class="panel panel-primary">
                <div class="panel-body">
                    <h3><?= number_format($grade_summary['average_grade'] ?? 0, 2) ?>%</h3>
                    <p>Average Grade</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="panel panel-success">
                <div class="panel-body">
                    <h3><?= $grade_summary['total_graded'] ?? 0 ?></h3>
                    <p>Graded Assignments</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="panel panel-warning">
                <div class="panel-body">
                    <h3><?= number_format($grade_summary['highest_grade'] ?? 0, 2) ?></h3>
                    <p>Highest Score</p>
                </div>
            </div>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading"><i class="fa fa-list"></i> All Grades</div>
        <div class="panel-body">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Assignment</th>
                        <th>Teacher</th>
                        <th>Marks</th>
                        <th>Grade</th>
                        <th>Feedback</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($grades) && !empty($grades)): ?>
                        <?php foreach ($grades as $grade): ?>
                            <tr>
                                <td><?= esc($grade['assignment_title'] ?? 'Assignment #' . $grade['assignment_id']) ?></td>
                                <td><?= esc($grade['teacher_name'] ?? 'Teacher') ?></td>
                                <td><?= esc($grade['marks_obtained']) ?>/<?= esc($grade['total_marks']) ?></td>
                                <td>
                                    <?php
                                    $gradeClass = 'success';
                            if ($grade['grade'] === 'F') {
                                $gradeClass = 'danger';
                            } elseif ($grade['grade'] === 'D') {
                                $gradeClass = 'warning';
                            }
                            ?>
                                    <span class="label label-<?= $gradeClass ?>"><?= esc($grade['grade']) ?></span>
                                </td>
                                <td><?= esc($grade['feedback'] ?? 'No feedback') ?></td>
                                <td><?= esc(substr($grade['created_at'], 0, 10)) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center">No grades available yet</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
