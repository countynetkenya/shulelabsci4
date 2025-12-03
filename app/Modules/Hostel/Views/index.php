<?= $this->extend('layouts/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800"><?= esc($title) ?></h1>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Hostels List</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Capacity</th>
                            <th>Location</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($hostels) && is_array($hostels)) : ?>
                            <?php foreach ($hostels as $hostel) : ?>
                                <tr>
                                    <td><?= esc($hostel['name']) ?></td>
                                    <td><?= esc($hostel['type']) ?></td>
                                    <td><?= esc($hostel['capacity']) ?></td>
                                    <td><?= esc($hostel['location']) ?></td>
                                    <td>
                                        <a href="#" class="btn btn-sm btn-info">View</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="5" class="text-center">No hostels found.</td>
                            </tr>
                        <?php endif ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
