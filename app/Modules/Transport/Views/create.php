<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Add Vehicle</h1>
        <a href="<?= site_url('transport') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="<?= site_url('transport/store') ?>" method="post">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label for="registration_number">Registration Number</label>
                    <input type="text" class="form-control <?= session('errors.registration_number') ? 'is-invalid' : '' ?>" id="registration_number" name="registration_number" value="<?= old('registration_number') ?>" required>
                    <?php if (session('errors.registration_number')): ?>
                        <div class="invalid-feedback"><?= session('errors.registration_number') ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="capacity">Capacity</label>
                        <input type="number" class="form-control <?= session('errors.capacity') ? 'is-invalid' : '' ?>" id="capacity" name="capacity" value="<?= old('capacity') ?>" required>
                        <?php if (session('errors.capacity')): ?>
                            <div class="invalid-feedback"><?= session('errors.capacity') ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="driver_name">Driver Name</label>
                        <input type="text" class="form-control <?= session('errors.driver_name') ? 'is-invalid' : '' ?>" id="driver_name" name="driver_name" value="<?= old('driver_name') ?>" required>
                        <?php if (session('errors.driver_name')): ?>
                            <div class="invalid-feedback"><?= session('errors.driver_name') ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Save Vehicle</button>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
