<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Add Security Log Entry</h1>
        <a href="<?= base_url('security') ?>" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <?php if (session()->getFlashdata('errors')): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach (session()->getFlashdata('errors') as $error): ?>
                            <li><?= esc($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="post" action="<?= base_url('security') ?>">
                <?= csrf_field() ?>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Identifier <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="identifier" required
                                   value="<?= old('identifier') ?>" placeholder="username or email">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>IP Address <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="ip_address" required
                                   value="<?= old('ip_address') ?>">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Attempt Type <span class="text-danger">*</span></label>
                            <select class="form-control" name="attempt_type" required>
                                <option value="login">Login</option>
                                <option value="2fa">2FA</option>
                                <option value="password_reset">Password Reset</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Was Successful?</label>
                            <div class="custom-control custom-checkbox mt-2">
                                <input type="checkbox" class="custom-control-input" id="was_successful" name="was_successful" value="1">
                                <label class="custom-control-label" for="was_successful">Success</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>User Agent</label>
                    <input type="text" class="form-control" name="user_agent" value="<?= old('user_agent') ?>">
                </div>

                <div class="form-group">
                    <label>Failure Reason</label>
                    <input type="text" class="form-control" name="failure_reason" value="<?= old('failure_reason') ?>">
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Create
                    </button>
                    <a href="<?= base_url('security') ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
