<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <!-- Admin Panel Header -->
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">
                Admin Panel
                <small>System Administration</small>
            </h1>
        </div>
    </div>

    <!-- Admin Welcome -->
    <div class="row">
        <div class="col-lg-12">
            <div class="alert alert-success">
                <i class="fa fa-shield"></i> Welcome to the Admin Panel, <strong><?= esc($user['name']) ?></strong>!
            </div>
        </div>
    </div>

    <!-- Admin Sections -->
    <div class="row">
        <div class="col-lg-3 col-md-6">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-3">
                            <i class="fa fa-users fa-5x"></i>
                        </div>
                        <div class="col-xs-9 text-right">
                            <div>User</div>
                            <div>Management</div>
                        </div>
                    </div>
                </div>
                <a href="#">
                    <div class="panel-footer">
                        <span class="pull-left">Manage Users</span>
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
                            <i class="fa fa-building fa-5x"></i>
                        </div>
                        <div class="col-xs-9 text-right">
                            <div>School</div>
                            <div>Management</div>
                        </div>
                    </div>
                </div>
                <a href="#">
                    <div class="panel-footer">
                        <span class="pull-left">Manage Schools</span>
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
                            <i class="fa fa-cogs fa-5x"></i>
                        </div>
                        <div class="col-xs-9 text-right">
                            <div>System</div>
                            <div>Settings</div>
                        </div>
                    </div>
                </div>
                <a href="#">
                    <div class="panel-footer">
                        <span class="pull-left">Configure System</span>
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
                            <i class="fa fa-database fa-5x"></i>
                        </div>
                        <div class="col-xs-9 text-right">
                            <div>Database</div>
                            <div>Management</div>
                        </div>
                    </div>
                </div>
                <a href="#">
                    <div class="panel-footer">
                        <span class="pull-left">Database Tools</span>
                        <span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
                        <div class="clearfix"></div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- System Overview -->
    <div class="row">
        <div class="col-lg-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="fa fa-info-circle"></i> System Overview
                </div>
                <div class="panel-body">
                    <table class="table table-striped">
                        <tbody>
                            <tr>
                                <td><strong>System Version:</strong></td>
                                <td>CI4 Standalone</td>
                            </tr>
                            <tr>
                                <td><strong>PHP Version:</strong></td>
                                <td><?= phpversion() ?></td>
                            </tr>
                            <tr>
                                <td><strong>CodeIgniter Version:</strong></td>
                                <td><?= \CodeIgniter\CodeIgniter::CI_VERSION ?></td>
                            </tr>
                            <tr>
                                <td><strong>Environment:</strong></td>
                                <td><?= ENVIRONMENT ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Logs -->
    <div class="row">
        <div class="col-lg-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="fa fa-list"></i> Recent System Logs
                </div>
                <div class="panel-body">
                    <p class="text-muted">No recent logs to display.</p>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
