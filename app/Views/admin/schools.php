<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <h1 class="page-header"><i class="fa fa-building"></i> School Management</h1>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>

    <div class="panel panel-default">
        <div class="panel-heading"><i class="fa fa-list"></i> All Schools</div>
        <div class="panel-body">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>School Name</th>
                        <th>Code</th>
                        <th>Address</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($schools) && !empty($schools)): ?>
                        <?php foreach ($schools as $school): ?>
                            <tr>
                                <td><?= esc($school['schoolID'] ?? $school['id']) ?></td>
                                <td><?= esc($school['school_name'] ?? $school['schoolName']) ?></td>
                                <td><?= esc($school['school_code'] ?? $school['schoolCode'] ?? 'N/A') ?></td>
                                <td><?= esc($school['address'] ?? 'N/A') ?></td>
                                <td><?= esc($school['phone'] ?? 'N/A') ?></td>
                                <td><?= esc($school['email'] ?? 'N/A') ?></td>
                                <td>
                                    <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#editSchool<?= $school['schoolID'] ?? $school['id'] ?>">
                                        <i class="fa fa-edit"></i> Edit
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center">No schools found</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
