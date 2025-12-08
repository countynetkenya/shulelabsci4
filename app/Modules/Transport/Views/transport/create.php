<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Add New Route</h1>
        <a href="<?= site_url('transport') ?>" class="d-none d-sm-inline-block btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Back to List
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Route Details</h6>
        </div>
        <div class="card-body">
            <?php if (session()->has('errors')): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach (session('errors') as $error): ?>
                            <li><?= esc($error) ?></li>
                        <?php endforeach ?>
                    </ul>
                </div>
            <?php endif ?>

            <form action="<?= site_url('transport/store') ?>" method="post">
                <?= csrf_field() ?>
                
                <div class="form-group">
                    <label for="route_name">Route Name</label>
                    <input type="text" class="form-control" id="route_name" name="route_name" value="<?= old('route_name') ?>" required>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="start_point">Start Point</label>
                            <input type="text" class="form-control" id="start_point" name="start_point" value="<?= old('start_point') ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="end_point">End Point</label>
                            <input type="text" class="form-control" id="end_point" name="end_point" value="<?= old('end_point') ?>" required>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="cost">Cost (KES)</label>
                    <input type="number" step="0.01" class="form-control" id="cost" name="cost" value="<?= old('cost') ?>" required>
                </div>

                <button type="submit" class="btn btn-primary">Create Route</button>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
