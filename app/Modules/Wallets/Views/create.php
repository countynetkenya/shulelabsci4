<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fa fa-wallet"></i> Create Wallet
        </h1>
        <a href="<?= base_url('wallets') ?>" class="btn btn-secondary btn-sm shadow-sm">
            <i class="fa fa-arrow-left fa-sm"></i> Back to List
        </a>
    </div>

    <?php if (session()->getFlashdata('errors')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Please fix the following errors:</strong>
            <ul class="mb-0 mt-2">
                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Wallet Information</h6>
        </div>
        <div class="card-body">
            <form action="<?= base_url('wallets/store') ?>" method="POST">
                <?= csrf_field() ?>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="user_id">User ID <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="user_id" name="user_id" value="<?= old('user_id') ?>" required>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="wallet_type">Wallet Type <span class="text-danger">*</span></label>
                            <select name="wallet_type" id="wallet_type" class="form-control" required>
                                <option value="">-- Select Type --</option>
                                <option value="student" <?= old('wallet_type') === 'student' ? 'selected' : '' ?>>Student</option>
                                <option value="parent" <?= old('wallet_type') === 'parent' ? 'selected' : '' ?>>Parent</option>
                                <option value="staff" <?= old('wallet_type') === 'staff' ? 'selected' : '' ?>>Staff</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="currency">Currency</label>
                            <input type="text" class="form-control" id="currency" name="currency" value="<?= old('currency', 'KES') ?>" maxlength="3">
                        </div>
                    </div>
                </div>

                <hr>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> Create Wallet
                    </button>
                    <a href="<?= base_url('wallets') ?>" class="btn btn-secondary">
                        <i class="fa fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
