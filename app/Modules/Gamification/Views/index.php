<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fa fa-trophy text-warning"></i> Gamification Dashboard
        </h1>
        <a href="<?= base_url('gamification/create') ?>" class="btn btn-primary btn-sm">
            <i class="fa fa-plus"></i> Create Badge
        </a>
    </div>

    <?php if (session()->getFlashdata('message')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('message') ?>
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    <?php endif; ?>

    <!-- Badges Section -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fa fa-medal"></i> Badges
            </h6>
        </div>
        <div class="card-body">
            <?php if (empty($badges)): ?>
                <p class="text-muted">No badges found. <a href="<?= base_url('gamification/create') ?>">Create your first badge</a></p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Code</th>
                                <th>Category</th>
                                <th>Tier</th>
                                <th>Points Reward</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($badges as $badge): ?>
                                <tr>
                                    <td><?= esc($badge['name']) ?></td>
                                    <td><code><?= esc($badge['code']) ?></code></td>
                                    <td>
                                        <span class="badge badge-info"><?= esc($badge['category']) ?></span>
                                    </td>
                                    <td>
                                        <?php
                                        $tierColors = [
                                            'bronze' => 'secondary',
                                            'silver' => 'light',
                                            'gold' => 'warning',
                                            'platinum' => 'primary',
                                            'diamond' => 'info'
                                        ];
                                        $color = $tierColors[$badge['tier']] ?? 'secondary';
                                        ?>
                                        <span class="badge badge-<?= $color ?>"><?= esc($badge['tier']) ?></span>
                                    </td>
                                    <td><?= esc($badge['points_reward']) ?> pts</td>
                                    <td>
                                        <?php if ($badge['is_active']): ?>
                                            <span class="badge badge-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Inactive</span>
                                        <?php endif; ?>
                                        <?php if ($badge['is_secret']): ?>
                                            <span class="badge badge-dark">Secret</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="<?= base_url('gamification/edit/' . $badge['id']) ?>" class="btn btn-sm btn-info">
                                            <i class="fa fa-edit"></i> Edit
                                        </a>
                                        <?php if ($badge['school_id'] !== null): ?>
                                            <a href="<?= base_url('gamification/delete/' . $badge['id']) ?>" 
                                               class="btn btn-sm btn-danger"
                                               onclick="return confirm('Are you sure you want to delete this badge?')">
                                                <i class="fa fa-trash"></i> Delete
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Achievements Section -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fa fa-star"></i> Achievements
            </h6>
        </div>
        <div class="card-body">
            <?php if (empty($achievements)): ?>
                <p class="text-muted">No achievements configured yet.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Code</th>
                                <th>Category</th>
                                <th>Criteria</th>
                                <th>Points Reward</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($achievements as $achievement): ?>
                                <tr>
                                    <td><?= esc($achievement['name']) ?></td>
                                    <td><code><?= esc($achievement['code']) ?></code></td>
                                    <td><span class="badge badge-info"><?= esc($achievement['category']) ?></span></td>
                                    <td><?= esc($achievement['criteria_type']) ?>: <?= esc($achievement['criteria_value']) ?></td>
                                    <td><?= esc($achievement['points_reward']) ?> pts</td>
                                    <td>
                                        <?php if ($achievement['is_active']): ?>
                                            <span class="badge badge-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent Points Section -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fa fa-coins"></i> Recent Point Transactions
            </h6>
        </div>
        <div class="card-body">
            <?php if (empty($recentPoints)): ?>
                <p class="text-muted">No point transactions yet.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>User ID</th>
                                <th>Points</th>
                                <th>Type</th>
                                <th>Source</th>
                                <th>Description</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentPoints as $point): ?>
                                <tr>
                                    <td><?= esc($point['user_id']) ?></td>
                                    <td>
                                        <?php if ($point['points'] > 0): ?>
                                            <span class="text-success">+<?= esc($point['points']) ?></span>
                                        <?php else: ?>
                                            <span class="text-danger"><?= esc($point['points']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge badge-secondary"><?= esc($point['type']) ?></span></td>
                                    <td><?= esc($point['source']) ?></td>
                                    <td><?= esc($point['description'] ?? '-') ?></td>
                                    <td><?= date('M d, Y H:i', strtotime($point['created_at'])) ?></td>
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
