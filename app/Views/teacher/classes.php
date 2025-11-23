<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <h1 class="page-header"><i class="fa fa-book"></i> My Classes</h1>
    
    <div class="row">
        <?php if (isset($classes) && !empty($classes)): ?>
            <?php foreach ($classes as $class): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4><?= esc($class['class_name'] ?? $class['classes'] ?? 'Class') ?></h4>
                        </div>
                        <div class="panel-body">
                            <p><strong>Class Code:</strong> <?= esc($class['class_code'] ?? $class['classesID'] ?? 'N/A') ?></p>
                            <p><strong>Section:</strong> <?= esc($class['section'] ?? 'N/A') ?></p>
                        </div>
                        <div class="panel-footer">
                            <a href="/teacher/class/<?= $class['classesID'] ?? $class['id'] ?>/students" class="btn btn-primary btn-sm">
                                <i class="fa fa-users"></i> View Students
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-lg-12">
                <div class="alert alert-info">
                    <i class="fa fa-info-circle"></i> You have no classes assigned yet.
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?= $this->endSection() ?>
