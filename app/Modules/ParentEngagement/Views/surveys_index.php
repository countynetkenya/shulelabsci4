<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">
        <?= esc($title) ?>
        <a href="<?= base_url('parent-engagement/surveys/create') ?>" class="btn btn-primary float-right">
            <i class="fa fa-plus"></i> Create Survey
        </a>
    </h1>

    <?php if (session()->getFlashdata('message')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('message') ?></div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-body">
            <?php if (!empty($surveys)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Audience</th>
                                <th>Responses</th>
                                <th>Period</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($surveys as $survey): ?>
                                <tr>
                                    <td><?= esc($survey['title']) ?></td>
                                    <td><span class="badge badge-info"><?= esc($survey['survey_type']) ?></span></td>
                                    <td><?= esc($survey['target_audience']) ?></td>
                                    <td><?= esc($survey['response_count']) ?></td>
                                    <td>
                                        <?= esc($survey['start_date']) ?> to <?= esc($survey['end_date']) ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= $survey['status'] === 'active' ? 'success' : 'secondary' ?>">
                                            <?= esc($survey['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-info" disabled title="Coming soon">View Results</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted">No surveys found. <a href="<?= base_url('parent-engagement/surveys/create') ?>">Create one now</a>.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
