<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Transport Management</h1>
        <a href="<?= site_url('transport/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add Route
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Routes & Vehicles</h6>
        </div>
        <div class="card-body">
            <?php if (session()->has('message')): ?>
                <div class="alert alert-success"><?= session('message') ?></div>
            <?php endif ?>
            <?php if (session()->has('error')): ?>
                <div class="alert alert-danger"><?= session('error') ?></div>
            <?php endif ?>
            
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Route Name</th>
                            <th>Start Point</th>
                            <th>End Point</th>
                            <th>Cost</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($routes as $route): ?>
                            <tr>
                                <td><?= esc($route['route_name']) ?></td>
                                <td><?= esc($route['start_point']) ?></td>
                                <td><?= esc($route['end_point']) ?></td>
                                <td><?= number_format($route['cost'], 2) ?></td>
                                <td>
                                    <a href="<?= site_url('transport/edit/' . $route['id']) ?>" class="btn btn-sm btn-info"><i class="fas fa-edit"></i></a>
                                    <a href="<?= site_url('transport/delete/' . $route['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
