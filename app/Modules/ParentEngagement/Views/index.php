<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Parent Engagement - Surveys</h1>
        <a href="<?= base_url('parent-engagement/create') ?>" class="btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> Create Survey
        </a>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= session()->getFlashdata('success') ?>
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">All Surveys</h6>
        </div>
        <div class="card-body">
            <?php if (empty($surveys)): ?>
                <p class="text-center text-muted">No surveys found. <a href="<?= base_url('parent-engagement/create') ?>">Create your first survey</a>.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Audience</th>
                                <th>Status</th>
                                <th>Responses</th>
                                <th>Period</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($surveys as $survey): ?>
                                <tr>
                                    <td><?= esc($survey['title']) ?></td>
                                    <td><span class="badge badge-info"><?= esc($survey['survey_type']) ?></span></td>
                                    <td><?= esc($survey['target_audience']) ?></td>
                                    <td>
                                        <?php
                                        $badge = match($survey['status']) {
                                            'active' => 'success',
                                            'draft' => 'secondary',
                                            'closed' => 'warning',
                                            'archived' => 'dark',
                                            default => 'secondary'
                                        };
                                ?>
                                        <span class="badge badge-<?= $badge ?>"><?= esc($survey['status']) ?></span>
                                    </td>
                                    <td><?= $survey['response_count'] ?></td>
                                    <td>
                                        <?= $survey['start_date'] ? date('Y-m-d', strtotime($survey['start_date'])) : '' ?>
                                        <?php if ($survey['end_date']): ?>
                                            - <?= date('Y-m-d', strtotime($survey['end_date'])) ?>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="<?= base_url('parent-engagement/' . $survey['id'] . '/edit') ?>" class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="<?= base_url('parent-engagement/' . $survey['id'] . '/delete') ?>" 
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('Delete this survey?')">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
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
