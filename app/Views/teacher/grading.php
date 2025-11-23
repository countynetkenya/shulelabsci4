<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <h1 class="page-header"><i class="fa fa-check-square"></i> Grading</h1>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-6">
            <div class="panel panel-default">
                <div class="panel-heading"><i class="fa fa-tasks"></i> Assignments for Grading</div>
                <div class="panel-body">
                    <?php if (isset($assignments) && !empty($assignments)): ?>
                        <div class="list-group">
                            <?php foreach ($assignments as $assignment): ?>
                                <div class="list-group-item">
                                    <h4><?= esc($assignment['title']) ?></h4>
                                    <p><small>Due: <?= esc($assignment['due_date']) ?> | Marks: <?= esc($assignment['total_marks']) ?></small></p>
                                    <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#gradeModal<?= $assignment['assignmentsID'] ?? $assignment['id'] ?>">
                                        <i class="fa fa-edit"></i> Grade Students
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No assignments available for grading</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="panel panel-default">
                <div class="panel-heading"><i class="fa fa-history"></i> Recent Grades</div>
                <div class="panel-body">
                    <?php if (isset($recent_grades) && !empty($recent_grades)): ?>
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Marks</th>
                                    <th>Grade</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_grades as $grade): ?>
                                    <tr>
                                        <td>Student #<?= esc($grade['student_id']) ?></td>
                                        <td><?= esc($grade['marks_obtained']) ?>/<?= esc($grade['total_marks']) ?></td>
                                        <td><span class="label label-success"><?= esc($grade['grade']) ?></span></td>
                                        <td><?= esc(substr($grade['created_at'], 0, 10)) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="text-muted">No grades submitted yet</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
