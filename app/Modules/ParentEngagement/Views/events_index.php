<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">
        <?= esc($title) ?>
        <a href="<?= base_url('parent-engagement/events/create') ?>" class="btn btn-primary float-right">
            <i class="fa fa-plus"></i> Create Event
        </a>
    </h1>

    <?php if (session()->getFlashdata('message')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('message') ?></div>
    <?php endif; ?>

    <div class="card shadow mb-4">
        <div class="card-body">
            <?php if (!empty($events)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Start Date/Time</th>
                                <th>Venue</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($events as $event): ?>
                                <tr>
                                    <td><?= esc($event['title']) ?></td>
                                    <td><span class="badge badge-info"><?= esc($event['event_type']) ?></span></td>
                                    <td><?= esc($event['start_datetime']) ?></td>
                                    <td><?= esc($event['venue'] ?? 'N/A') ?></td>
                                    <td>
                                        <span class="badge badge-<?= $event['status'] === 'published' ? 'success' : 'secondary' ?>">
                                            <?= esc($event['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="<?= base_url('parent-engagement/events/edit/' . $event['id']) ?>" 
                                           class="btn btn-sm btn-warning">Edit</a>
                                        <a href="<?= base_url('parent-engagement/events/delete/' . $event['id']) ?>" 
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('Are you sure?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted">No events found. <a href="<?= base_url('parent-engagement/events/create') ?>">Create one now</a>.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
