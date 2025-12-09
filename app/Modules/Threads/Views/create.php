<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">New Message</h1>
        <a href="<?= site_url('threads') ?>" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="<?= site_url('threads/store') ?>" method="post">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label for="recipient_id">Recipient ID</label>
                    <input type="number" class="form-control <?= session('errors.recipient_id') ? 'is-invalid' : '' ?>" id="recipient_id" name="recipient_id" value="<?= old('recipient_id') ?>" required>
                    <?php if (session('errors.recipient_id')): ?>
                        <div class="invalid-feedback"><?= session('errors.recipient_id') ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="subject">Subject</label>
                    <input type="text" class="form-control <?= session('errors.subject') ? 'is-invalid' : '' ?>" id="subject" name="subject" value="<?= old('subject') ?>" required>
                    <?php if (session('errors.subject')): ?>
                        <div class="invalid-feedback"><?= session('errors.subject') ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="body">Message Body</label>
                    <textarea class="form-control <?= session('errors.body') ? 'is-invalid' : '' ?>" id="body" name="body" rows="5" required><?= old('body') ?></textarea>
                    <?php if (session('errors.body')): ?>
                        <div class="invalid-feedback"><?= session('errors.body') ?></div>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn btn-primary">Send Message</button>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
