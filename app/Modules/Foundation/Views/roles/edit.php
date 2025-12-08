<?= $this->extend('layouts/app') ?>

<?= $this->section('header') ?>
    Edit Role
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="max-w-2xl mx-auto">
    <div class="md:flex md:items-center md:justify-between mb-6">
        <div class="min-w-0 flex-1">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">Edit Role Details</h2>
        </div>
        <div class="mt-4 flex md:mt-0 md:ml-4">
            <a href="<?= site_url('system/roles') ?>" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">Back</a>
        </div>
    </div>

    <form action="<?= site_url('system/roles/update/' . $role['id']) ?>" method="post" class="space-y-8 divide-y divide-gray-200 bg-white p-6 shadow rounded-lg">
        <?= csrf_field() ?>
        
        <div class="space-y-8 divide-y divide-gray-200">
            <div>
                <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                    <div class="sm:col-span-4">
                        <label for="role_name" class="block text-sm font-medium text-gray-700">Role Name</label>
                        <div class="mt-1">
                            <input type="text" name="role_name" id="role_name" value="<?= esc($role['role_name']) ?>" required class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        </div>
                    </div>

                    <div class="sm:col-span-4">
                        <label for="role_slug" class="block text-sm font-medium text-gray-700">Role Slug</label>
                        <div class="mt-1">
                            <input type="text" name="role_slug" id="role_slug" value="<?= esc($role['role_slug']) ?>" required class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        </div>
                        <p class="mt-2 text-sm text-gray-500">Unique identifier (e.g., school-admin, teacher).</p>
                    </div>

                    <div class="sm:col-span-6">
                        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                        <div class="mt-1">
                            <textarea id="description" name="description" rows="3" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"><?= esc($role['description']) ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="pt-5">
            <div class="flex justify-end">
                <a href="<?= site_url('system/roles') ?>" class="rounded-md border border-gray-300 bg-white py-2 px-4 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">Cancel</a>
                <button type="submit" class="ml-3 inline-flex justify-center rounded-md border border-transparent bg-blue-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">Update Role</button>
            </div>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
