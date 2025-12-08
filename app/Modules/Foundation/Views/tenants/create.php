<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="max-w-2xl mx-auto p-6">
    <div class="md:flex md:items-center md:justify-between mb-6">
        <div class="min-w-0 flex-1">
            <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight">Create New School</h2>
        </div>
    </div>

    <form action="/system/tenants" method="POST" class="space-y-8 divide-y divide-gray-200 bg-white p-6 shadow rounded-lg">
        <div class="space-y-8 divide-y divide-gray-200">
            <div>
                <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                    <div class="sm:col-span-4">
                        <label for="name" class="block text-sm font-medium text-gray-700">School Name</label>
                        <div class="mt-1">
                            <input type="text" name="name" id="name" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        </div>
                    </div>

                    <div class="sm:col-span-2">
                        <label for="code" class="block text-sm font-medium text-gray-700">School Code</label>
                        <div class="mt-1">
                            <input type="text" name="code" id="code" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        </div>
                    </div>

                    <div class="sm:col-span-4">
                        <label for="domain" class="block text-sm font-medium text-gray-700">Domain / Subdomain</label>
                        <div class="mt-1 flex rounded-md shadow-sm">
                            <input type="text" name="domain" id="domain" class="block w-full min-w-0 flex-1 rounded-none rounded-l-md border-gray-300 focus:border-blue-500 focus:ring-blue-500 sm:text-sm" placeholder="myschool">
                            <span class="inline-flex items-center rounded-r-md border border-l-0 border-gray-300 bg-gray-50 px-3 text-gray-500 sm:text-sm">.shulelabs.local</span>
                        </div>
                    </div>

                    <div class="sm:col-span-6">
                        <label for="admin_email" class="block text-sm font-medium text-gray-700">Admin Email</label>
                        <div class="mt-1">
                            <input type="email" name="admin_email" id="admin_email" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        </div>
                        <p class="mt-2 text-sm text-gray-500">An invitation will be sent to this email.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="pt-5">
            <div class="flex justify-end">
                <a href="/system/tenants" class="rounded-md border border-gray-300 bg-white py-2 px-4 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">Cancel</a>
                <button type="submit" class="ml-3 inline-flex justify-center rounded-md border border-transparent bg-blue-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">Create School</button>
            </div>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
