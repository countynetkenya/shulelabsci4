<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= esc($title) ?></h1>
        <a href="<?= base_url('orchestration/create') ?>" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Create Workflow</a>
    </div>
    <?php if (isset($statistics)): ?>
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-primary shadow h-100 py-2"><div class="card-body"><div class="row no-gutters align-items-center"><div class="col mr-2"><div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total</div><div class="h5 mb-0 font-weight-bold text-gray-800"><?= $statistics['total_workflows'] ?></div></div><div class="col-auto"><i class="fas fa-sitemap fa-2x text-gray-300"></i></div></div></div></div></div>
        <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-success shadow h-100 py-2"><div class="card-body"><div class="row no-gutters align-items-center"><div class="col mr-2"><div class="text-xs font-weight-bold text-success text-uppercase mb-1">Completed</div><div class="h5 mb-0 font-weight-bold text-gray-800"><?= $statistics['completed_workflows'] ?></div></div><div class="col-auto"><i class="fas fa-check-circle fa-2x text-gray-300"></i></div></div></div></div></div>
        <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-info shadow h-100 py-2"><div class="card-body"><div class="row no-gutters align-items-center"><div class="col mr-2"><div class="text-xs font-weight-bold text-info text-uppercase mb-1">Running</div><div class="h5 mb-0 font-weight-bold text-gray-800"><?= $statistics['running_workflows'] ?></div></div><div class="col-auto"><i class="fas fa-sync fa-2x text-gray-300"></i></div></div></div></div></div>
        <div class="col-xl-3 col-md-6 mb-4"><div class="card border-left-danger shadow h-100 py-2"><div class="card-body"><div class="row no-gutters align-items-center"><div class="col mr-2"><div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Failed</div><div class="h5 mb-0 font-weight-bold text-gray-800"><?= $statistics['failed_workflows'] ?></div></div><div class="col-auto"><i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i></div></div></div></div></div>
    </div>
    <?php endif; ?>
    <?php if (session()->has('success')): ?><div class="alert alert-success alert-dismissible fade show"><?= session('success') ?><button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div><?php endif; ?>
    <?php if (session()->has('error')): ?><div class="alert alert-danger alert-dismissible fade show"><?= session('error') ?><button type="button" class="close" data-dismiss="alert"><span>&times;</span></button></div><?php endif; ?>
    <div class="card shadow mb-4">
        <div class="card-header py-3"><h6 class="m-0 font-weight-bold text-primary">Workflows</h6></div>
        <div class="card-body">
            <?php if (empty($workflows)): ?><p class="text-center text-muted">No workflows found.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%">
                        <thead><tr><th>ID</th><th>Name</th><th>Status</th><th>Progress</th><th>Created</th><th>Actions</th></tr></thead>
                        <tbody>
                            <?php foreach ($workflows as $wf): ?>
                                <tr>
                                    <td><?= esc($wf['workflow_id']) ?></td>
                                    <td><?= esc($wf['name']) ?></td>
                                    <td><span class="badge badge-<?= ['pending'=>'warning','running'=>'info','completed'=>'success','failed'=>'danger','paused'=>'secondary'][$wf['status']] ?? 'secondary' ?>"><?= esc($wf['status']) ?></span></td>
                                    <td><?= $wf['current_step'] ?> / <?= $wf['total_steps'] ?></td>
                                    <td><?= date('Y-m-d H:i', strtotime($wf['created_at'])) ?></td>
                                    <td>
                                        <a href="<?= base_url('orchestration/edit/' . $wf['id']) ?>" class="btn btn-sm btn-info"><i class="fas fa-edit"></i></a>
                                        <a href="<?= base_url('orchestration/delete/' . $wf['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete?')"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
