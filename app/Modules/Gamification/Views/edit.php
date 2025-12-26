<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fa fa-trophy"></i> Edit Badge
        </h1>
        <a href="<?= base_url('gamification') ?>" class="btn btn-secondary btn-sm">
            <i class="fa fa-arrow-left"></i> Back
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Badge Information</h6>
                </div>
                <div class="card-body">
                    <?php if (session()->getFlashdata('errors')): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                                    <li><?= esc($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form action="<?= base_url('gamification/update/' . $badge['id']) ?>" method="post">
                        <?= csrf_field() ?>

                        <div class="form-group">
                            <label for="name">Badge Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?= old('name', $badge['name']) ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="code">Badge Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="code" name="code" 
                                   value="<?= old('code', $badge['code']) ?>" placeholder="e.g., PERFECT_ATTENDANCE" required>
                            <small class="form-text text-muted">Unique identifier (letters, numbers, underscores)</small>
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control" id="description" name="description" 
                                      rows="3"><?= old('description', $badge['description']) ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="category">Category <span class="text-danger">*</span></label>
                                    <select class="form-control" id="category" name="category" required>
                                        <option value="">-- Select Category --</option>
                                        <option value="academic" <?= old('category', $badge['category']) === 'academic' ? 'selected' : '' ?>>Academic</option>
                                        <option value="attendance" <?= old('category', $badge['category']) === 'attendance' ? 'selected' : '' ?>>Attendance</option>
                                        <option value="behavior" <?= old('category', $badge['category']) === 'behavior' ? 'selected' : '' ?>>Behavior</option>
                                        <option value="sports" <?= old('category', $badge['category']) === 'sports' ? 'selected' : '' ?>>Sports</option>
                                        <option value="leadership" <?= old('category', $badge['category']) === 'leadership' ? 'selected' : '' ?>>Leadership</option>
                                        <option value="special" <?= old('category', $badge['category']) === 'special' ? 'selected' : '' ?>>Special</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tier">Tier</label>
                                    <select class="form-control" id="tier" name="tier">
                                        <option value="bronze" <?= old('tier', $badge['tier']) === 'bronze' ? 'selected' : '' ?>>Bronze</option>
                                        <option value="silver" <?= old('tier', $badge['tier']) === 'silver' ? 'selected' : '' ?>>Silver</option>
                                        <option value="gold" <?= old('tier', $badge['tier']) === 'gold' ? 'selected' : '' ?>>Gold</option>
                                        <option value="platinum" <?= old('tier', $badge['tier']) === 'platinum' ? 'selected' : '' ?>>Platinum</option>
                                        <option value="diamond" <?= old('tier', $badge['tier']) === 'diamond' ? 'selected' : '' ?>>Diamond</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="points_reward">Points Reward</label>
                            <input type="number" class="form-control" id="points_reward" name="points_reward" 
                                   value="<?= old('points_reward', $badge['points_reward']) ?>" min="0">
                            <small class="form-text text-muted">Points awarded when this badge is earned</small>
                        </div>

                        <div class="form-group form-check">
                            <input type="checkbox" class="form-check-input" id="is_secret" name="is_secret" 
                                   value="1" <?= old('is_secret', $badge['is_secret']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_secret">
                                Secret Badge (hidden until earned)
                            </label>
                        </div>

                        <div class="form-group form-check">
                            <input type="checkbox" class="form-check-input" id="is_active" name="is_active" 
                                   value="1" <?= old('is_active', $badge['is_active']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_active">
                                Active
                            </label>
                        </div>

                        <hr>

                        <div class="form-group mb-0">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save"></i> Update Badge
                            </button>
                            <a href="<?= base_url('gamification') ?>" class="btn btn-secondary">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
