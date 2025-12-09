<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800"><?= esc($title) ?></h1>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="post" action="<?= base_url('parent-engagement/campaigns/store') ?>">
                <?= csrf_field() ?>
                
                <?php if (session()->getFlashdata('errors')): ?>
                    <div class="alert alert-danger">
                        <ul>
                            <?php foreach (session()->getFlashdata('errors') as $error): ?>
                                <li><?= esc($error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="name">Campaign Name *</label>
                    <input type="text" name="name" id="name" class="form-control" 
                           value="<?= old('name') ?>" required>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea name="description" id="description" class="form-control" rows="4"><?= old('description') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="target_amount">Target Amount (KES) *</label>
                    <input type="number" step="0.01" name="target_amount" id="target_amount" 
                           class="form-control" value="<?= old('target_amount') ?>" required>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="start_date">Start Date *</label>
                            <input type="date" name="start_date" id="start_date" 
                                   class="form-control" value="<?= old('start_date') ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="end_date">End Date *</label>
                            <input type="date" name="end_date" id="end_date" 
                                   class="form-control" value="<?= old('end_date') ?>" required>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info">
                    <i class="fa fa-info-circle"></i> The campaign will be created in draft status. 
                    You can activate it later from the campaigns list.
                </div>

                <button type="submit" class="btn btn-primary">Create Campaign</button>
                <a href="<?= base_url('parent-engagement/campaigns') ?>" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
