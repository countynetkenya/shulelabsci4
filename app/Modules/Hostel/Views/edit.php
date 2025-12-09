<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Room</h1>
        <a href="<?= site_url('hostel') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="<?= site_url('hostel/update/' . $room['id']) ?>" method="post">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label for="room_number">Room Number</label>
                    <input type="text" class="form-control <?= session('errors.room_number') ? 'is-invalid' : '' ?>" id="room_number" name="room_number" value="<?= old('room_number', $room['room_number']) ?>" required>
                    <?php if (session('errors.room_number')): ?>
                        <div class="invalid-feedback"><?= session('errors.room_number') ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="capacity">Capacity</label>
                        <input type="number" class="form-control <?= session('errors.capacity') ? 'is-invalid' : '' ?>" id="capacity" name="capacity" value="<?= old('capacity', $room['capacity']) ?>" required>
                        <?php if (session('errors.capacity')): ?>
                            <div class="invalid-feedback"><?= session('errors.capacity') ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="type">Type</label>
                        <select class="form-control" id="type" name="type">
                            <option value="dorm" <?= $room['type'] == 'dorm' ? 'selected' : '' ?>>Dormitory</option>
                            <option value="private" <?= $room['type'] == 'private' ? 'selected' : '' ?>>Private Room</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="status">Status</label>
                    <select class="form-control" id="status" name="status">
                        <option value="available" <?= $room['status'] == 'available' ? 'selected' : '' ?>>Available</option>
                        <option value="full" <?= $room['status'] == 'full' ? 'selected' : '' ?>>Full</option>
                        <option value="maintenance" <?= $room['status'] == 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Update Room</button>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
