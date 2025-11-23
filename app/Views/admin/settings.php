<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <h1 class="page-header"><i class="fa fa-cogs"></i> System Settings</h1>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>

    <div class="panel panel-default">
        <div class="panel-heading"><i class="fa fa-wrench"></i> Application Settings</div>
        <div class="panel-body">
            <form action="/admin/settings/update" method="post">
                <?= csrf_field() ?>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Site Name</label>
                            <input type="text" name="settings[site_name]" class="form-control" value="ShuleLabs CI4" placeholder="Site Name">
                        </div>
                        <div class="form-group">
                            <label>Site Email</label>
                            <input type="email" name="settings[site_email]" class="form-control" value="admin@shulelabs.local" placeholder="Site Email">
                        </div>
                        <div class="form-group">
                            <label>Timezone</label>
                            <select name="settings[timezone]" class="form-control">
                                <option value="Africa/Nairobi">Africa/Nairobi (EAT)</option>
                                <option value="UTC">UTC</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Date Format</label>
                            <select name="settings[date_format]" class="form-control">
                                <option value="Y-m-d">YYYY-MM-DD</option>
                                <option value="d/m/Y">DD/MM/YYYY</option>
                                <option value="m/d/Y">MM/DD/YYYY</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Enable Maintenance Mode</label>
                            <select name="settings[maintenance_mode]" class="form-control">
                                <option value="0">No</option>
                                <option value="1">Yes</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Max Upload Size (MB)</label>
                            <input type="number" name="settings[max_upload_size]" class="form-control" value="10" placeholder="10">
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Save Settings</button>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
