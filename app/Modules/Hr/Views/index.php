<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">HR Module</h3>
                </div>
                <div class="card-body">
                    <p>Welcome to the HR Module Dashboard.</p>
                    <a href="<?= site_url('hr/employees') ?>" class="btn btn-primary">Manage Employees</a>
                    <a href="<?= site_url('hr/payroll/approvals') ?>" class="btn btn-secondary">Payroll Approvals</a>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
