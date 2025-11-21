<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Dashboard Header -->
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">
                Dashboard
                <small><?= esc($usertype) ?></small>
            </h1>
        </div>
    </div>

    <!-- Welcome Message -->
    <div class="row">
        <div class="col-lg-12">
            <div class="alert alert-info">
                <i class="fa fa-home"></i> Welcome back, <strong><?= esc($user['name']) ?></strong>!
            </div>
        </div>
    </div>

    <!-- Statistics Boxes -->
    <div class="row">
        <div class="col-lg-3 col-md-6">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-3">
                            <i class="fa fa-users fa-5x"></i>
                        </div>
                        <div class="col-xs-9 text-right">
                            <div class="huge">-</div>
                            <div>Students</div>
                        </div>
                    </div>
                </div>
                <a href="#">
                    <div class="panel-footer">
                        <span class="pull-left">View Details</span>
                        <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                        <div class="clearfix"></div>
                    </div>
                </a>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="panel panel-green">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-3">
                            <i class="fa fa-graduation-cap fa-5x"></i>
                        </div>
                        <div class="col-xs-9 text-right">
                            <div class="huge">-</div>
                            <div>Teachers</div>
                        </div>
                    </div>
                </div>
                <a href="#">
                    <div class="panel-footer">
                        <span class="pull-left">View Details</span>
                        <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                        <div class="clearfix"></div>
                    </div>
                </a>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="panel panel-yellow">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-3">
                            <i class="fa fa-book fa-5x"></i>
                        </div>
                        <div class="col-xs-9 text-right">
                            <div class="huge">-</div>
                            <div>Classes</div>
                        </div>
                    </div>
                </div>
                <a href="#">
                    <div class="panel-footer">
                        <span class="pull-left">View Details</span>
                        <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                        <div class="clearfix"></div>
                    </div>
                </a>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="panel panel-red">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-3">
                            <i class="fa fa-money fa-5x"></i>
                        </div>
                        <div class="col-xs-9 text-right">
                            <div class="huge">-</div>
                            <div>Fees</div>
                        </div>
                    </div>
                </div>
                <a href="#">
                    <div class="panel-footer">
                        <span class="pull-left">View Details</span>
                        <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                        <div class="clearfix"></div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="row">
        <div class="col-lg-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="fa fa-link"></i> Quick Links
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-3">
                            <a href="#" class="btn btn-default btn-lg btn-block">
                                <i class="fa fa-users"></i> Students
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="#" class="btn btn-default btn-lg btn-block">
                                <i class="fa fa-graduation-cap"></i> Teachers
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="#" class="btn btn-default btn-lg btn-block">
                                <i class="fa fa-book"></i> Classes
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="#" class="btn btn-default btn-lg btn-block">
                                <i class="fa fa-cog"></i> Settings
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row">
        <div class="col-lg-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="fa fa-bell"></i> Recent Activity
                </div>
                <div class="panel-body">
                    <p class="text-muted">No recent activity to display.</p>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
