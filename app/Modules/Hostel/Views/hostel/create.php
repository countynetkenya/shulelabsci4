<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Add Hostel</h1>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Hostel Details</h6>
        </div>
        <div class="card-body">
            <?php if (session()->has('errors')): ?>
                <div class="alert alert-danger">
                    <?php foreach (session('errors') as $error): ?>
                        <p><?= $error ?></p>
                    <?php endforeach ?>
                </div>
            <?php endif ?>

            <form action="<?= site_url('hostel/store') ?>" method="post">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label for="name">Hostel Name</label>
                    <input type="text" class="form-control" id="name" name="name" value="<?= old('name') ?>" required>
                </div>
                <div class="form-group">
                    <label for="type">Type</label>
                    <select class="form-control" id="type" name="type" required>
                        <option value="Boys" <?= old('type') == 'Boys' ? 'selected' : '' ?>>Boys</option>
                        <option value="Girls" <?= old('type') == 'Girls' ? 'selected' : '' ?>>Girls</option>
                        <option value="Mixed" <?= old('type') == 'Mixed' ? 'selected' : '' ?>>Mixed</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="capacity">Capacity</label>
                    <input type="number" class="form-control" id="capacity" name="capacity" value="<?= old('capacity') ?>" required>
                </div>
                <div class="form-group">
                    <label for="location">Location</label>
                    <input type="text" class="form-control" id="location" name="location" value="<?= old('location') ?>" required>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea class="form-control" id="description" name="description"><?= old('description') ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Save Hostel</button>
                <a href="<?= site_url('hostel') ?>" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
