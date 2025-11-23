<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <h1 class="page-header"><i class="fa fa-user-friends"></i> Parent Dashboard</h1>
    
    <div class="row">
        <div class="col-lg-12">
            <div class="panel panel-default">
                <div class="panel-heading"><i class="fa fa-child"></i> My Children</div>
                <div class="panel-body">
                    <?php if (isset($children) && !empty($children)): ?>
                        <div class="row">
                            <?php foreach ($children as $child): ?>
                                <div class="col-lg-4 col-md-6">
                                    <div class="panel panel-primary">
                                        <div class="panel-heading">
                                            <h4><?= esc($child['full_name']) ?></h4>
                                        </div>
                                        <div class="panel-body">
                                            <p><strong>Class:</strong> <?= esc($child['class_name'] ?? 'N/A') ?></p>
                                            <p><strong>Email:</strong> <?= esc($child['email']) ?></p>
                                        </div>
                                        <div class="panel-footer">
                                            <a href="/parent/child/<?= $child['id'] ?>/grades" class="btn btn-success btn-sm">
                                                <i class="fa fa-trophy"></i> Grades
                                            </a>
                                            <a href="/parent/child/<?= $child['id'] ?>/attendance" class="btn btn-info btn-sm">
                                                <i class="fa fa-calendar-check"></i> Attendance
                                            </a>
                                            <a href="/parent/child/<?= $child['id'] ?>/assignments" class="btn btn-warning btn-sm">
                                                <i class="fa fa-tasks"></i> Assignments
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle"></i> No children linked to your account yet.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <?php if (isset($recent_updates) && !empty($recent_updates)): ?>
    <div class="row">
        <div class="col-lg-12">
            <div class="panel panel-default">
                <div class="panel-heading"><i class="fa fa-bell"></i> Recent Updates</div>
                <div class="panel-body">
                    <ul class="list-group">
                        <?php foreach ($recent_updates as $update): ?>
                            <li class="list-group-item">
                                New grade posted for Student #<?= esc($update['student_id']) ?>
                                <span class="pull-right">
                                    <span class="label label-success"><?= esc($update['grade']) ?></span>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
