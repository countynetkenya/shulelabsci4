<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <h1 class="page-header"><i class="fa fa-users"></i> My Children</h1>

    <div class="row">
        <?php if (isset($children) && !empty($children)): ?>
            <?php foreach ($children as $child): ?>
                <div class="col-lg-6">
                    <div class="panel panel-primary">
                        <div class="panel-heading">
                            <h3><?= esc($child['full_name']) ?></h3>
                        </div>
                        <div class="panel-body">
                            <p><strong>Class:</strong> <?= esc($child['class_name'] ?? 'N/A') ?></p>
                            <p><strong>Email:</strong> <?= esc($child['email']) ?></p>
                            <p><strong>Student ID:</strong> <?= esc($child['id']) ?></p>
                        </div>
                        <div class="panel-footer">
                            <div class="btn-group btn-group-justified">
                                <a href="/parent/child/<?= $child['id'] ?>/grades" class="btn btn-success">
                                    <i class="fa fa-trophy"></i> Grades
                                </a>
                                <a href="/parent/child/<?= $child['id'] ?>/attendance" class="btn btn-info">
                                    <i class="fa fa-calendar-check"></i> Attendance
                                </a>
                                <a href="/parent/child/<?= $child['id'] ?>/assignments" class="btn btn-warning">
                                    <i class="fa fa-tasks"></i> Assignments
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-lg-12">
                <div class="alert alert-info">
                    <i class="fa fa-info-circle"></i> No children linked to your account yet. Please contact the school administrator.
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>
