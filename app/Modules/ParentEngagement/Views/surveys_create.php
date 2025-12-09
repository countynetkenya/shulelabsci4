<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800"><?= esc($title) ?></h1>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="post" action="<?= base_url('parent-engagement/surveys/store') ?>">
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
                    <label for="title">Survey Title *</label>
                    <input type="text" name="title" id="title" class="form-control" 
                           value="<?= old('title') ?>" required>
                </div>

                <div class="form-group">
                    <label for="survey_type">Survey Type *</label>
                    <select name="survey_type" id="survey_type" class="form-control" required>
                        <option value="">Select Type</option>
                        <option value="feedback" <?= old('survey_type') === 'feedback' ? 'selected' : '' ?>>Feedback</option>
                        <option value="poll" <?= old('survey_type') === 'poll' ? 'selected' : '' ?>>Poll</option>
                        <option value="evaluation" <?= old('survey_type') === 'evaluation' ? 'selected' : '' ?>>Evaluation</option>
                        <option value="custom" <?= old('survey_type') === 'custom' ? 'selected' : '' ?>>Custom</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="target_audience">Target Audience *</label>
                    <select name="target_audience" id="target_audience" class="form-control" required>
                        <option value="">Select Audience</option>
                        <option value="all_parents" <?= old('target_audience') === 'all_parents' ? 'selected' : '' ?>>All Parents</option>
                        <option value="class_parents" <?= old('target_audience') === 'class_parents' ? 'selected' : '' ?>>Class Parents</option>
                        <option value="specific" <?= old('target_audience') === 'specific' ? 'selected' : '' ?>>Specific</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea name="description" id="description" class="form-control" rows="3"><?= old('description') ?></textarea>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="start_date">Start Date</label>
                            <input type="date" name="start_date" id="start_date" 
                                   class="form-control" value="<?= old('start_date') ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="end_date">End Date</label>
                            <input type="date" name="end_date" id="end_date" 
                                   class="form-control" value="<?= old('end_date') ?>">
                        </div>
                    </div>
                </div>

                <div class="form-check mb-3">
                    <input type="checkbox" name="is_anonymous" id="is_anonymous" 
                           class="form-check-input" value="1" <?= old('is_anonymous') ? 'checked' : '' ?>>
                    <label class="form-check-label" for="is_anonymous">
                        Allow Anonymous Responses
                    </label>
                </div>

                <button type="submit" class="btn btn-primary">Create Survey</button>
                <a href="<?= base_url('parent-engagement/surveys') ?>" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
