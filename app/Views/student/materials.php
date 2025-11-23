<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <h1 class="page-header">
        <i class="fa fa-book-open"></i> Course Materials - <?= esc($course['class_name'] ?? 'Course') ?>
        <a href="/student/courses" class="btn btn-default pull-right">
            <i class="fa fa-arrow-left"></i> Back to Courses
        </a>
    </h1>

    <div class="row">
        <div class="col-lg-8">
            <div class="panel panel-default">
                <div class="panel-heading"><i class="fa fa-tasks"></i> Assignments</div>
                <div class="panel-body">
                    <?php if (isset($assignments) && !empty($assignments)): ?>
                        <div class="list-group">
                            <?php foreach ($assignments as $assignment): ?>
                                <div class="list-group-item">
                                    <h4><?= esc($assignment['title']) ?></h4>
                                    <p><?= esc($assignment['description'] ?? 'No description') ?></p>
                                    <p>
                                        <strong>Due:</strong> <?= esc($assignment['due_date']) ?> | 
                                        <strong>Marks:</strong> <?= esc($assignment['total_marks']) ?>
                                    </p>
                                    <?php if ($assignment['status'] === 'active'): ?>
                                        <a href="/student/assignments" class="btn btn-sm btn-primary">
                                            <i class="fa fa-upload"></i> Submit Assignment
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No assignments posted yet</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="panel panel-default">
                <div class="panel-heading"><i class="fa fa-bullhorn"></i> Announcements</div>
                <div class="panel-body">
                    <?php if (isset($announcements) && !empty($announcements)): ?>
                        <?php foreach ($announcements as $announcement): ?>
                            <div class="alert alert-info">
                                <h5><?= esc($announcement['title']) ?></h5>
                                <p><?= esc($announcement['message']) ?></p>
                                <small class="text-muted"><?= esc($announcement['created_at']) ?></small>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">No announcements</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
