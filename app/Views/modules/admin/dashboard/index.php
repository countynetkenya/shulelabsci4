<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <h1 class="h3 mb-4"><?= esc($title) ?></h1>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body">
                    <h5 class="card-title text-primary">Total Students</h5>
                    <h2 class="display-4"><?= $totalStudents ?? 0 ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body">
                    <h5 class="card-title text-success">Total Teachers</h5>
                    <h2 class="display-4"><?= $totalTeachers ?? 0 ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body">
                    <h5 class="card-title text-info">Total Classes</h5>
                    <h2 class="display-4"><?= $totalClasses ?? 0 ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body">
                    <h5 class="card-title text-warning">Fee Collection</h5>
                    <h3><?= $feeCollection['collection_rate'] ?? 0 ?>%</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- School Info -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5>School Information</h5>
                </div>
                <div class="card-body">
                    <?php if (isset($schoolInfo)): ?>
                        <dl class="row">
                            <dt class="col-sm-3">School Name:</dt>
                            <dd class="col-sm-9"><?= esc($schoolInfo->name) ?></dd>
                            
                            <dt class="col-sm-3">School Code:</dt>
                            <dd class="col-sm-9"><?= esc($schoolInfo->code ?? 'N/A') ?></dd>
                            
                            <dt class="col-sm-3">Email:</dt>
                            <dd class="col-sm-9"><?= esc($schoolInfo->email ?? 'N/A') ?></dd>
                            
                            <dt class="col-sm-3">Phone:</dt>
                            <dd class="col-sm-9"><?= esc($schoolInfo->phone ?? 'N/A') ?></dd>
                        </dl>
                    <?php else: ?>
                        <p class="text-muted">School information not available</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5>Quick Actions</h5>
                </div>
                <div class="card-body">
                    <a href="/admin/students/create" class="btn btn-primary me-2">
                        <i class="fas fa-user-plus"></i> Add Student
                    </a>
                    <a href="/admin/teachers/create" class="btn btn-success me-2">
                        <i class="fas fa-chalkboard-teacher"></i> Add Teacher
                    </a>
                    <a href="/admin/classes/create" class="btn btn-info me-2">
                        <i class="fas fa-plus"></i> Add Class
                    </a>
                    <a href="/admin/finance" class="btn btn-warning">
                        <i class="fas fa-money-bill"></i> Finance
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
