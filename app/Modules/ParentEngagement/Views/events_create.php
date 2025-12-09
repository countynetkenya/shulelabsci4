<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800"><?= esc($title) ?></h1>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="post" action="<?= base_url('parent-engagement/events/store') ?>">
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
                    <label for="title">Event Title *</label>
                    <input type="text" name="title" id="title" class="form-control" 
                           value="<?= old('title') ?>" required>
                </div>

                <div class="form-group">
                    <label for="event_type">Event Type *</label>
                    <select name="event_type" id="event_type" class="form-control" required>
                        <option value="">Select Type</option>
                        <option value="academic" <?= old('event_type') === 'academic' ? 'selected' : '' ?>>Academic</option>
                        <option value="sports" <?= old('event_type') === 'sports' ? 'selected' : '' ?>>Sports</option>
                        <option value="cultural" <?= old('event_type') === 'cultural' ? 'selected' : '' ?>>Cultural</option>
                        <option value="meeting" <?= old('event_type') === 'meeting' ? 'selected' : '' ?>>Meeting</option>
                        <option value="fundraising" <?= old('event_type') === 'fundraising' ? 'selected' : '' ?>>Fundraising</option>
                        <option value="other" <?= old('event_type') === 'other' ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea name="description" id="description" class="form-control" rows="3"><?= old('description') ?></textarea>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="start_datetime">Start Date/Time *</label>
                            <input type="datetime-local" name="start_datetime" id="start_datetime" 
                                   class="form-control" value="<?= old('start_datetime') ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="end_datetime">End Date/Time</label>
                            <input type="datetime-local" name="end_datetime" id="end_datetime" 
                                   class="form-control" value="<?= old('end_datetime') ?>">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="venue">Venue</label>
                    <input type="text" name="venue" id="venue" class="form-control" 
                           value="<?= old('venue') ?>">
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="max_attendees">Max Attendees</label>
                            <input type="number" name="max_attendees" id="max_attendees" 
                                   class="form-control" value="<?= old('max_attendees') ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="fee">Fee (KES)</label>
                            <input type="number" step="0.01" name="fee" id="fee" 
                                   class="form-control" value="<?= old('fee', '0.00') ?>">
                        </div>
                    </div>
                </div>

                <div class="form-check mb-3">
                    <input type="checkbox" name="registration_required" id="registration_required" 
                           class="form-check-input" value="1" <?= old('registration_required') ? 'checked' : '' ?>>
                    <label class="form-check-label" for="registration_required">
                        Registration Required
                    </label>
                </div>

                <button type="submit" class="btn btn-primary">Create Event</button>
                <a href="<?= base_url('parent-engagement/events') ?>" class="btn btn-secondary">Cancel</a>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
