<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <h1 class="page-header"><i class="fa fa-graduation-cap"></i> Student Dashboard</h1>
    
    <div class="row">
        <div class="col-lg-3 col-md-6">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-3"><i class="fa fa-book fa-5x"></i></div>
                        <div class="col-xs-9 text-right">
                            <div class="huge"><?= $stats['total_courses'] ?? 0 ?></div>
                            <div>My Courses</div>
                        </div>
                    </div>
                </div>
                <a href="/student/courses">
                    <div class="panel-footer">
                        <span class="pull-left">View Courses</span>
                        <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                        <div class="clearfix"></div>
                    </div>
                </a>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="panel panel-warning">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-3"><i class="fa fa-tasks fa-5x"></i></div>
                        <div class="col-xs-9 text-right">
                            <div class="huge"><?= $stats['pending_assignments'] ?? 0 ?></div>
                            <div>Pending Assignments</div>
                        </div>
                    </div>
                </div>
                <a href="/student/assignments">
                    <div class="panel-footer">
                        <span class="pull-left">View Assignments</span>
                        <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                        <div class="clearfix"></div>
                    </div>
                </a>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="panel panel-success">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-3"><i class="fa fa-trophy fa-5x"></i></div>
                        <div class="col-xs-9 text-right">
                            <div class="huge"><?= $stats['average_grade'] ?? 0 ?>%</div>
                            <div>Average Grade</div>
                        </div>
                    </div>
                </div>
                <a href="/student/grades">
                    <div class="panel-footer">
                        <span class="pull-left">View Grades</span>
                        <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                        <div class="clearfix"></div>
                    </div>
                </a>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-lg-6">
            <div class="panel panel-default">
                <div class="panel-heading"><i class="fa fa-calendar"></i> Pending Assignments</div>
                <div class="panel-body">
                    <?php if (isset($pending_assignments) && !empty($pending_assignments)): ?>
                        <ul class="list-group">
                            <?php foreach ($pending_assignments as $assignment): ?>
                                <li class="list-group-item">
                                    <strong><?= esc($assignment['title']) ?></strong>
                                    <span class="pull-right text-muted">Due: <?= esc($assignment['due_date']) ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted">No pending assignments</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="panel panel-default">
                <div class="panel-heading"><i class="fa fa-trophy"></i> Recent Grades</div>
                <div class="panel-body">
                    <?php if (isset($recent_grades) && !empty($recent_grades)): ?>
                        <ul class="list-group">
                            <?php foreach ($recent_grades as $grade): ?>
                                <li class="list-group-item">
                                    Assignment #<?= esc($grade['assignment_id']) ?>
                                    <span class="pull-right">
                                        <span class="label label-success"><?= esc($grade['grade']) ?></span>
                                        <?= esc($grade['marks_obtained']) ?>/<?= esc($grade['total_marks']) ?>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted">No grades yet</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
