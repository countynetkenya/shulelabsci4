<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <h1 class="page-header">
        <i class="fa fa-users"></i> Students in <?= esc($class['class_name'] ?? 'Class') ?>
        <a href="/teacher/classes" class="btn btn-default pull-right">
            <i class="fa fa-arrow-left"></i> Back to Classes
        </a>
    </h1>

    <div class="panel panel-default">
        <div class="panel-heading"><i class="fa fa-list"></i> Student Roster</div>
        <div class="panel-body">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Student Name</th>
                        <th>Email</th>
                        <th>Enrollment Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($students) && !empty($students)): ?>
                        <?php foreach ($students as $index => $student): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= esc($student['full_name'] ?? $student->full_name ?? 'N/A') ?></td>
                                <td><?= esc($student['email'] ?? $student->email ?? 'N/A') ?></td>
                                <td><?= esc($student['created_at'] ?? $student->created_at ?? 'N/A') ?></td>
                                <td><span class="label label-success">Active</span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center">No students enrolled in this class</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
