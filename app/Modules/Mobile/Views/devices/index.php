<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= esc($title) ?></h1>
        <a href="<?= base_url('mobile/devices/create') ?>" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Register Device</a>
    </div>
    <?php if (isset($statistics)): ?>
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body"><div class="row no-gutters align-items-center">
                    <div class="col mr-2"><div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Devices</div><div class="h5 mb-0 font-weight-bold text-gray-800"><?= $statistics['total_devices'] ?></div></div>
                    <div class="col-auto"><i class="fas fa-mobile-alt fa-2x text-gray-300"></i></div>
                </div></div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body"><div class="row no-gutters align-items-center">
                    <div class="col mr-2"><div class="text-xs font-weight-bold text-success text-uppercase mb-1">Active</div><div class="h5 mb-0 font-weight-bold text-gray-800"><?= $statistics['active_devices'] ?></div></div>
                    <div class="col-auto"><i class="fas fa-check-circle fa-2x text-gray-300"></i></div>
                </div></div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body"><div class="row no-gutters align-items-center">
                    <div class="col mr-2"><div class="text-xs font-weight-bold text-info text-uppercase mb-1">iOS</div><div class="h5 mb-0 font-weight-bold text-gray-800"><?= $statistics['ios_devices'] ?></div></div>
                    <div class="col-auto"><i class="fab fa-apple fa-2x text-gray-300"></i></div>
                </div></div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body"><div class="row no-gutters align-items-center">
                    <div class="col mr-2"><div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Android</div><div class="h5 mb-0 font-weight-bold text-gray-800"><?= $statistics['android_devices'] ?></div></div>
                    <div class="col-auto"><i class="fab fa-android fa-2x text-gray-300"></i></div>
                </div></div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <?php if (session()->has('success')): ?><div class="alert alert-success alert-dismissible fade show"><?= session('success') ?><button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div><?php endif; ?>
    <?php if (session()->has('error')): ?><div class="alert alert-danger alert-dismissible fade show"><?= session('error') ?><button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div><?php endif; ?>
    <div class="card shadow mb-4">
        <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Registered Devices</h6></div>
        <div class="card-body">
            <?php if (empty($devices)): ?>
                <p class="text-center text-muted">No devices found.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%">
                        <thead><tr><th>Device ID</th><th>User</th><th>Name</th><th>Type</th><th>Status</th><th>Last Active</th><th>Actions</th></tr></thead>
                        <tbody>
                            <?php foreach ($devices as $device): ?>
                                <tr>
                                    <td><?= esc($device['device_id']) ?></td>
                                    <td><?= esc($device['username'] ?? 'N/A') ?></td>
                                    <td><?= esc($device['device_name']) ?></td>
                                    <td><span class="badge badge-secondary"><?= esc($device['device_type']) ?></span></td>
                                    <td><span class="badge badge-<?= $device['is_active'] ? 'success' : 'secondary' ?>"><?= $device['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                                    <td><?= $device['last_active_at'] ? date('Y-m-d H:i', strtotime($device['last_active_at'])) : 'Never' ?></td>
                                    <td>
                                        <a href="<?= base_url('mobile/devices/edit/' . $device['id']) ?>" class="btn btn-sm btn-info"><i class="fas fa-edit"></i></a>
                                        <a href="<?= base_url('mobile/devices/delete/' . $device['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this device?')"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
