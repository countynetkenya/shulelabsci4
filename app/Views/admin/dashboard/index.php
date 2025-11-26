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
                    <h5 class="card-title text-primary">Total Schools</h5>
                    <h2 class="display-4"><?= $totalSchools ?? 0 ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body">
                    <h5 class="card-title text-success">Total Users</h5>
                    <h2 class="display-4"><?= $totalUsers ?? 0 ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body">
                    <h5 class="card-title text-info">Total Teachers</h5>
                    <h2 class="display-4"><?= $totalTeachers ?? 0 ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body">
                    <h5 class="card-title text-warning">Total Students</h5>
                    <h2 class="display-4"><?= $totalStudents ?? 0 ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- System Statistics -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>System Statistics</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Total Assignments
                            <span class="badge bg-primary rounded-pill"><?= $systemStats['total_assignments'] ?? 0 ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Library Books
                            <span class="badge bg-success rounded-pill"><?= $systemStats['total_library_books'] ?? 0 ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Pending Payments
                            <span class="badge bg-warning rounded-pill"><?= $systemStats['pending_payments'] ?? 0 ?></span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Recent Activity</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($recentActivity)): ?>
                        <div class="list-group">
                            <?php foreach (array_slice($recentActivity, 0, 5) as $activity): ?>
                                <div class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1"><?= esc($activity['username']) ?></h6>
                                        <small><?= date('M d, Y H:i', strtotime($activity['last_login'] ?? 'now')) ?></small>
                                    </div>
                                    <small><?= esc($activity['email']) ?></small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No recent activity</p>
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
                    <a href="/admin/schools/create" class="btn btn-primary me-2">
                        <i class="fas fa-plus"></i> Add School
                    </a>
                    <a href="/admin/users/create" class="btn btn-success me-2">
                        <i class="fas fa-user-plus"></i> Add User
                    </a>
                    <a href="/admin/schools" class="btn btn-info me-2">
                        <i class="fas fa-school"></i> Manage Schools
                    </a>
                    <a href="/admin/users" class="btn btn-secondary">
                        <i class="fas fa-users"></i> Manage Users
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
