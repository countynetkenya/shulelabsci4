<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <h1 class="page-header">
        <i class="fa fa-tasks"></i> Assignments - <?= esc($child['full_name'] ?? 'Student') ?>
        <a href="/parent/children" class="btn btn-default pull-right">
            <i class="fa fa-arrow-left"></i> Back to Children
        </a>
    </h1>

    <div class="row">
        <div class="col-lg-4 col-md-6">
            <div class="panel panel-primary">
                <div class="panel-body text-center">
                    <h2><?= $assignment_summary['total'] ?? 0 ?></h2>
                    <p>Total Assignments</p>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="panel panel-warning">
                <div class="panel-body text-center">
                    <h2><?= $assignment_summary['pending'] ?? 0 ?></h2>
                    <p>Pending</p>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6">
            <div class="panel panel-success">
                <div class="panel-body text-center">
                    <h2><?= $assignment_summary['completed'] ?? 0 ?></h2>
                    <p>Completed</p>
                </div>
            </div>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading"><i class="fa fa-list"></i> All Assignments</div>
        <div class="panel-body">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Assignment</th>
                        <th>Due Date</th>
                        <th>Total Marks</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($assignments) && !empty($assignments)): ?>
                        <?php foreach ($assignments as $assignment): ?>
                            <tr>
                                <td><?= esc($assignment['title']) ?></td>
                                <td><?= esc($assignment['due_date']) ?></td>
                                <td><?= esc($assignment['total_marks']) ?></td>
                                <td>
                                    <?php if ($assignment['status'] === 'active'): ?>
                                        <span class="label label-warning">Pending</span>
                                    <?php else: ?>
                                        <span class="label label-success">Closed</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center">No assignments available</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
