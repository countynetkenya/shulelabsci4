<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-chalkboard-teacher text-primary"></i> Teachers</h1>
        <a href="<?= site_url('teachers/create') ?>" class="btn btn-primary"><i class="fas fa-plus"></i> Add Teacher</a>
    </div>
    <?php if (session()->getFlashdata('message')): ?>
    <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle"></i> <?= session()->getFlashdata('message') ?><button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div>
    <?php endif; ?>
    <div class="card shadow mb-4">
        <div class="card-body">
            <table class="table table-bordered table-hover">
                <thead class="thead-light">
                    <tr><th>Employee ID</th><th>Name</th><th>Department</th><th>Email</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php if (!empty($teachers)): foreach ($teachers as $teacher): ?>
                    <tr>
                        <td><?= esc($teacher['employee_id'] ?? '—') ?></td>
                        <td><strong><?= esc($teacher['first_name']) ?> <?= esc($teacher['last_name']) ?></strong></td>
                        <td><?= esc($teacher['department'] ?? '—') ?></td>
                        <td><?= esc($teacher['email'] ?? '—') ?></td>
                        <td><span class="badge badge-<?= $teacher['status'] === 'active' ? 'success' : 'secondary' ?>"><?= ucfirst($teacher['status']) ?></span></td>
                        <td><a href="<?= site_url('teachers/edit/' . $teacher['id']) ?>" class="btn btn-sm btn-info"><i class="fas fa-edit"></i></a>
                            <a href="<?= site_url('teachers/delete/' . $teacher['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this teacher?')"><i class="fas fa-trash"></i></a></td>
                    </tr>
                    <?php endforeach;
                    else: ?>
                    <tr><td colspan="6" class="text-center py-4">No teachers found. <a href="<?= site_url('teachers/create') ?>" class="btn btn-primary mt-3"><i class="fas fa-plus"></i> Add First Teacher</a></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
