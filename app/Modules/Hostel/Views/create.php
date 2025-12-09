<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Add Room</h1>
        <a href="<?= site_url('hostel') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="<?= site_url('hostel/store') ?>" method="post">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label for="room_number">Room Number</label>
                    <input type="text" class="form-control <?= session('errors.room_number') ? 'is-invalid' : '' ?>" id="room_number" name="room_number" value="<?= old('room_number') ?>" required>
                    <?php if (session('errors.room_number')): ?>
                        <div class="invalid-feedback"><?= session('errors.room_number') ?></div>
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
                        <label for="type">Type</label>
                        <select class="form-control" id="type" name="type">
                            <option value="dorm">Dormitory</option>
                            <option value="private">Private Room</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Save Room</button>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
