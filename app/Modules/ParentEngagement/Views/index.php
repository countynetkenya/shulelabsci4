<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800"><?= esc($title) ?></h1>
    
    <?php if (session()->getFlashdata('message')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('message') ?></div>
    <?php endif; ?>

    <div class="row">
        <!-- Surveys -->
        <div class="col-md-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fa fa-poll"></i> Recent Surveys
                        <a href="<?= base_url('parent-engagement/surveys/create') ?>" class="btn btn-sm btn-primary float-right">Create New</a>
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($surveys)): ?>
                        <ul class="list-group">
                            <?php foreach ($surveys as $survey): ?>
                                <li class="list-group-item">
                                    <strong><?= esc($survey['title']) ?></strong>
                                    <span class="badge badge-<?= $survey['status'] === 'active' ? 'success' : 'secondary' ?> float-right">
                                        <?= esc($survey['status']) ?>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <a href="<?= base_url('parent-engagement/surveys') ?>" class="btn btn-sm btn-link">View All</a>
                    <?php else: ?>
                        <p class="text-muted">No surveys available.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Events -->
        <div class="col-md-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fa fa-calendar"></i> Upcoming Events
                        <a href="<?= base_url('parent-engagement/events/create') ?>" class="btn btn-sm btn-primary float-right">Create New</a>
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($events)): ?>
                        <ul class="list-group">
                            <?php foreach ($events as $event): ?>
                                <li class="list-group-item">
                                    <strong><?= esc($event['title']) ?></strong>
                                    <small class="text-muted d-block"><?= esc($event['start_datetime']) ?></small>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <a href="<?= base_url('parent-engagement/events') ?>" class="btn btn-sm btn-link">View All</a>
                    <?php else: ?>
                        <p class="text-muted">No upcoming events.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Fundraising Campaigns -->
        <div class="col-md-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fa fa-donate"></i> Active Campaigns
                        <a href="<?= base_url('parent-engagement/campaigns/create') ?>" class="btn btn-sm btn-primary float-right">Create New</a>
                    </h6>
                </div>
                <div class="card-body">
                    <?php if (!empty($campaigns)): ?>
                        <ul class="list-group">
                            <?php foreach ($campaigns as $campaign): ?>
                                <li class="list-group-item">
                                    <strong><?= esc($campaign['name']) ?></strong>
                                    <div class="progress mt-2" style="height: 20px;">
                                        <?php 
                                        $percentage = ($campaign['target_amount'] > 0) ? 
                                            min(100, ($campaign['raised_amount'] / $campaign['target_amount']) * 100) : 0;
                                        ?>
                                        <div class="progress-bar" role="progressbar" 
                                             style="width: <?= number_format($percentage, 0) ?>%">
                                            <?= number_format($percentage, 0) ?>%
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <a href="<?= base_url('parent-engagement/campaigns') ?>" class="btn btn-sm btn-link">View All</a>
                    <?php else: ?>
                        <p class="text-muted">No active campaigns.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
