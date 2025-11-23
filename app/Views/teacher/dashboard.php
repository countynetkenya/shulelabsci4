<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <h1 class="page-header"><i class="fa fa-chalkboard-teacher"></i> Teacher Dashboard</h1>
    
    <div class="row">
        <div class="col-lg-3 col-md-6">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-3"><i class="fa fa-book fa-5x"></i></div>
                        <div class="col-xs-9 text-right">
                            <div class="huge"><?= $stats['total_classes'] ?? 0 ?></div>
                            <div>My Classes</div>
                        </div>
                    </div>
                </div>
                <a href="/teacher/classes">
                    <div class="panel-footer">
                        <span class="pull-left">View Classes</span>
                        <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                        <div class="clearfix"></div>
                    </div>
                </a>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="panel panel-info">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-3"><i class="fa fa-tasks fa-5x"></i></div>
                        <div class="col-xs-9 text-right">
                            <div class="huge"><?= $stats['total_assignments'] ?? 0 ?></div>
                            <div>Assignments</div>
                        </div>
                    </div>
                </div>
                <a href="/teacher/assignments">
                    <div class="panel-footer">
                        <span class="pull-left">Manage Assignments</span>
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
                        <div class="col-xs-3"><i class="fa fa-check-square fa-5x"></i></div>
                        <div class="col-xs-9 text-right">
                            <div class="huge"><?= $stats['pending_grading'] ?? 0 ?></div>
                            <div>Pending Grading</div>
                        </div>
                    </div>
                </div>
                <a href="/teacher/grading">
                    <div class="panel-footer">
                        <span class="pull-left">Grade Assignments</span>
                        <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                        <div class="clearfix"></div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
