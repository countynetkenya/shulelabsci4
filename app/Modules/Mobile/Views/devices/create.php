<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= esc($title) ?></h1>
        <a href="<?= base_url('mobile/devices') ?>" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
    </div>
    <?php if (session()->has('errors')): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach (session('errors') as $error): ?><li><?= esc($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
    <div class="card shadow mb-4">
        <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Device Details</h6></div>
        <div class="card-body">
            <form action="<?= base_url('mobile/devices/store') ?>" method="post">
                <?= csrf_field() ?>
                <div class="form-group"><label>Device ID <span class="text-danger">*</span></label><input type="text" class="form-control" name="device_id" value="<?= old('device_id') ?>" required></div>
                <div class="form-group"><label>Device Name</label><input type="text" class="form-control" name="device_name" value="<?= old('device_name') ?>"></div>
                <div class="form-group"><label>Device Type <span class="text-danger">*</span></label><select class="form-control" name="device_type" required><option value="">-- Select --</option><option value="ios" <?= old('device_type') === 'ios' ? 'selected' : '' ?>>iOS</option><option value="android" <?= old('device_type') === 'android' ? 'selected' : '' ?>>Android</option><option value="web" <?= old('device_type') === 'web' ? 'selected' : '' ?>>Web</option></select></div>
                <div class="form-group"><label>OS Version</label><input type="text" class="form-control" name="os_version" value="<?= old('os_version') ?>"></div>
                <div class="form-group"><label>App Version</label><input type="text" class="form-control" name="app_version" value="<?= old('app_version') ?>"></div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Register Device</button>
                    <a href="<?= base_url('mobile/devices') ?>" class="btn btn-secondary"><i class="fas fa-times"></i> Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
