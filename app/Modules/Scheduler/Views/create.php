<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Add Event</h1>
        <a href="<?= site_url('scheduler') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="<?= site_url('scheduler/store') ?>" method="post">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label for="title">Event Title</label>
                    <input type="text" class="form-control <?= session('errors.title') ? 'is-invalid' : '' ?>" id="title" name="title" value="<?= old('title') ?>" required>
                    <?php if (session('errors.title')): ?>
                        <div class="invalid-feedback"><?= session('errors.title') ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="start_time">Start Time</label>
                        <input type="datetime-local" class="form-control <?= session('errors.start_time') ? 'is-invalid' : '' ?>" id="start_time" name="start_time" value="<?= old('start_time') ?>" required>
                        <?php if (session('errors.start_time')): ?>
                            <div class="invalid-feedback"><?= session('errors.start_time') ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="end_time">End Time</label>
                        <input type="datetime-local" class="form-control <?= session('errors.end_time') ? 'is-invalid' : '' ?>" id="end_time" name="end_time" value="<?= old('end_time') ?>" required>
                        <?php if (session('errors.end_time')): ?>
                            <div class="invalid-feedback"><?= session('errors.end_time') ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-group">
                    <label for="location">Location</label>
                    <input type="text" class="form-control <?= session('errors.location') ? 'is-invalid' : '' ?>" id="location" name="location" value="<?= old('location') ?>">
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="3"><?= old('description') ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary">Save Event</button>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
