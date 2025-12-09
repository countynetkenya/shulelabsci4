<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800"><?= esc($title) ?></h1>
    
    <div class="row">
        <!-- Courses Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Courses
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">--</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-book fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lessons Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Lessons
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">--</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enrollments Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Enrollments
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">--</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-graduate fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Completion Rate Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Avg. Completion
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">--%</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Quick Actions -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <a href="<?= base_url('lms/courses') ?>" class="list-group-item list-group-item-action">
                            <i class="fa fa-book"></i> Manage Courses
                        </a>
                        <a href="<?= base_url('lms/courses/new') ?>" class="list-group-item list-group-item-action">
                            <i class="fa fa-plus"></i> Create New Course
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Activity</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted">No recent activity.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Learning Management Features -->
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Learning Management Features</h6>
                </div>
                <div class="card-body">
                    <p>The LMS module provides comprehensive tools for managing online and blended learning:</p>
                    <ul>
                        <li><strong>Course Management:</strong> Create and organize courses with structured content</li>
                        <li><strong>Lesson Planning:</strong> Build engaging lessons with multimedia content</li>
                        <li><strong>Student Enrollment:</strong> Manage student access to courses</li>
                        <li><strong>Progress Tracking:</strong> Monitor student completion and engagement</li>
                        <li><strong>Assessments:</strong> Create quizzes and assignments</li>
                        <li><strong>Gradebook:</strong> Track and manage student grades</li>
                        <li><strong>Attendance:</strong> Record and monitor class attendance</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
