<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Edit Survey</h1>
        <a href="<?= base_url('parent-engagement') ?>" class="btn btn-sm btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <div class="card shadow mb-4">
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

            <form method="post" action="<?= base_url('parent-engagement/' . $survey['id']) ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="_method" value="PUT">
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="title" required 
                                   value="<?= old('title', $survey['title']) ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Survey Type <span class="text-danger">*</span></label>
                            <select class="form-control" name="survey_type" required>
                                <option value="feedback" <?= old('survey_type', $survey['survey_type']) === 'feedback' ? 'selected' : '' ?>>Feedback</option>
                                <option value="poll" <?= old('survey_type', $survey['survey_type']) === 'poll' ? 'selected' : '' ?>>Poll</option>
                                <option value="evaluation" <?= old('survey_type', $survey['survey_type']) === 'evaluation' ? 'selected' : '' ?>>Evaluation</option>
                                <option value="custom" <?= old('survey_type', $survey['survey_type']) === 'custom' ? 'selected' : '' ?>>Custom</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea class="form-control" name="description" rows="3"><?= old('description', $survey['description']) ?></textarea>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Target Audience <span class="text-danger">*</span></label>
                            <select class="form-control" name="target_audience" required>
                                <option value="all_parents" <?= old('target_audience', $survey['target_audience']) === 'all_parents' ? 'selected' : '' ?>>All Parents</option>
                                <option value="class_parents" <?= old('target_audience', $survey['target_audience']) === 'class_parents' ? 'selected' : '' ?>>Class Parents</option>
                                <option value="specific" <?= old('target_audience', $survey['target_audience']) === 'specific' ? 'selected' : '' ?>>Specific</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Start Date</label>
                            <input type="date" class="form-control" name="start_date" 
                                   value="<?= old('start_date', $survey['start_date']) ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>End Date</label>
                            <input type="date" class="form-control" name="end_date" 
                                   value="<?= old('end_date', $survey['end_date']) ?>">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Status</label>
                    <select class="form-control" name="status">
                        <option value="draft" <?= old('status', $survey['status']) === 'draft' ? 'selected' : '' ?>>Draft</option>
                        <option value="active" <?= old('status', $survey['status']) === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="closed" <?= old('status', $survey['status']) === 'closed' ? 'selected' : '' ?>>Closed</option>
                        <option value="archived" <?= old('status', $survey['status']) === 'archived' ? 'selected' : '' ?>>Archived</option>
                    </select>
                </div>

                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="is_anonymous" name="is_anonymous" value="1"
                               <?= old('is_anonymous', $survey['is_anonymous']) ? 'checked' : '' ?>>
                        <label class="custom-control-label" for="is_anonymous">Anonymous Responses</label>
                    </div>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Survey
                    </button>
                    <a href="<?= base_url('parent-engagement') ?>" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
