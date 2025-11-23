<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <h1 class="page-header"><i class="fa fa-users"></i> My Courses</h1>
    
    <div class="row">
        <?php if (isset($courses) && !empty($courses)): ?>
            <?php foreach ($courses as $course): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4><?= esc($course['class_name'] ?? $course['classes'] ?? 'Course') ?></h4>
                        </div>
                        <div class="panel-body">
                            <p><strong>Teacher:</strong> <?= esc($course['teacher_name'] ?? 'N/A') ?></p>
                            <p><strong>Section:</strong> <?= esc($course['section'] ?? 'N/A') ?></p>
                        </div>
                        <div class="panel-footer">
                            <a href="/student/course/<?= $course['classesID'] ?? $course['class_id'] ?>/materials" class="btn btn-primary btn-sm">
                                <i class="fa fa-folder-open"></i> View Materials
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-lg-12">
                <div class="alert alert-info">
                    <i class="fa fa-info-circle"></i> You are not enrolled in any courses yet.
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>
