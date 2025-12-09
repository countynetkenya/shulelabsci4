<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Transport Vehicles</h1>
        <a href="<?= site_url('transport/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Vehicle
        </a>
    </div>

    <?php if (session()->getFlashdata('message')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('message') ?></div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Registration</th>
                            <th>Capacity</th>
                            <th>Driver</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($vehicles)): ?>
                            <?php foreach ($vehicles as $vehicle): ?>
                                <tr>
                                    <td><?= esc($vehicle['registration_number']) ?></td>
                                    <td><?= esc($vehicle['capacity']) ?></td>
                                    <td><?= esc($vehicle['driver_name']) ?></td>
                                    <td>
                                        <span class="badge badge-<?= $vehicle['status'] == 'active' ? 'success' : 'secondary' ?>">
                                            <?= esc(ucfirst($vehicle['status'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="<?= site_url('transport/edit/' . $vehicle['id']) ?>" class="btn btn-sm btn-info">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="<?= site_url('transport/delete/' . $vehicle['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">No vehicles found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
