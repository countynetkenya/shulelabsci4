<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">
        <?= esc($title) ?>
        <a href="<?= base_url('parent-engagement/campaigns/create') ?>" class="btn btn-primary float-right">
            <i class="fa fa-plus"></i> Create Campaign
        </a>
    </h1>

    <?php if (session()->getFlashdata('message')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('message') ?></div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-body">
            <?php if (!empty($campaigns)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Campaign Name</th>
                                <th>Target Amount</th>
                                <th>Raised Amount</th>
                                <th>Progress</th>
                                <th>Period</th>
                                <th>Status</th>
                                <th>Donors</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($campaigns as $campaign): ?>
                                <tr>
                                    <td><?= esc($campaign['name']) ?></td>
                                    <td>KES <?= number_format($campaign['target_amount'], 2) ?></td>
                                    <td>KES <?= number_format($campaign['raised_amount'], 2) ?></td>
                                    <td>
                                        <div class="progress" style="height: 25px;">
                                            <?php 
                                            $percentage = $campaign['progress_percentage'];
                                            $colorClass = $percentage >= 75 ? 'success' : ($percentage >= 50 ? 'warning' : 'danger');
                                            ?>
                                            <div class="progress-bar bg-<?= $colorClass ?>" 
                                                 role="progressbar" style="width: <?= number_format($percentage, 0) ?>%">
                                                <?= number_format($percentage, 0) ?>%
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?= esc($campaign['start_date']) ?> to <?= esc($campaign['end_date']) ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= $campaign['status'] === 'active' ? 'success' : 'secondary' ?>">
                                            <?= esc($campaign['status']) ?>
                                        </span>
                                    </td>
                                    <td><?= esc($campaign['donor_count']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted">No campaigns found. <a href="<?= base_url('parent-engagement/campaigns/create') ?>">Create one now</a>.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
