<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fa fa-history"></i> View Audit Event
        </h1>
        <a href="<?= base_url('audit') ?>" class="btn btn-secondary btn-sm">
            <i class="fa fa-arrow-left"></i> Back to List
        </a>
    </div>

    <?php if (session()->has('errors')): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach (session('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Audit Event Details (Read-Only)</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <dl class="row">
                        <dt class="col-sm-4">Event ID:</dt>
                        <dd class="col-sm-8"><?= esc($event['id']) ?></dd>

                        <dt class="col-sm-4">Event Type:</dt>
                        <dd class="col-sm-8">
                            <span class="badge badge-primary"><?= esc($event['event_type']) ?></span>
                        </dd>

                        <dt class="col-sm-4">Entity:</dt>
                        <dd class="col-sm-8">
                            <?php if ($event['entity_type']): ?>
                                <?= esc($event['entity_type']) ?> #<?= esc($event['entity_id']) ?>
                            <?php else: ?>
                                <span class="text-muted">N/A</span>
                            <?php endif; ?>
                        </dd>

                        <dt class="col-sm-4">User ID:</dt>
                        <dd class="col-sm-8"><?= esc($event['user_id'] ?? 'System') ?></dd>

                        <dt class="col-sm-4">IP Address:</dt>
                        <dd class="col-sm-8"><?= esc($event['ip_address'] ?? 'N/A') ?></dd>

                        <dt class="col-sm-4">Timestamp:</dt>
                        <dd class="col-sm-8"><?= date('Y-m-d H:i:s', strtotime($event['created_at'])) ?></dd>
                    </dl>
                </div>

                <div class="col-md-6">
                    <dl class="row">
                        <dt class="col-sm-4">Trace ID:</dt>
                        <dd class="col-sm-8"><code><?= esc($event['trace_id'] ?? 'N/A') ?></code></dd>

                        <dt class="col-sm-4">Event Key:</dt>
                        <dd class="col-sm-8"><code><?= esc($event['event_key'] ?? 'N/A') ?></code></dd>

                        <dt class="col-sm-4">User Agent:</dt>
                        <dd class="col-sm-8" style="font-size: 0.85em;">
                            <?= esc(substr($event['user_agent'] ?? 'N/A', 0, 80)) ?>
                        </dd>

                        <dt class="col-sm-4">Request URI:</dt>
                        <dd class="col-sm-8"><?= esc($event['request_uri'] ?? 'N/A') ?></dd>
                    </dl>
                </div>
            </div>

            <hr>

            <?php if (!empty($event['before_state']) || !empty($event['after_state'])): ?>
                <div class="row">
                    <div class="col-md-6">
                        <h6>Before State</h6>
                        <pre class="bg-light p-3 border"><?= esc(json_encode($event['before_state'] ?? [], JSON_PRETTY_PRINT)) ?></pre>
                    </div>
                    <div class="col-md-6">
                        <h6>After State</h6>
                        <pre class="bg-light p-3 border"><?= esc(json_encode($event['after_state'] ?? [], JSON_PRETTY_PRINT)) ?></pre>
                    </div>
                </div>
                <hr>
            <?php endif; ?>

            <?php if (!empty($event['changed_fields'])): ?>
                <div class="mb-3">
                    <h6>Changed Fields</h6>
                    <pre class="bg-light p-3 border"><?= esc(json_encode($event['changed_fields'], JSON_PRETTY_PRINT)) ?></pre>
                </div>
                <hr>
            <?php endif; ?>

            <!-- Optional: Add compliance notes form -->
            <form method="post" action="<?= base_url('audit/update/' . $event['id']) ?>">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label for="metadata_json">Compliance Notes (JSON)</label>
                    <textarea name="metadata_json" id="metadata_json" class="form-control" rows="3" 
                              placeholder='{"note": "Reviewed for compliance"}'><?= esc($event['metadata_json'] ?? '') ?></textarea>
                    <small class="form-text text-muted">Add compliance notes in JSON format (optional).</small>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> Update Notes
                    </button>
                    <a href="<?= base_url('audit') ?>" class="btn btn-secondary">
                        <i class="fa fa-arrow-left"></i> Back to List
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
