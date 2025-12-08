<?= $this->extend('layouts/app') ?>

<?= $this->section('content') ?>
<div class="p-6" x-data="{ activeTab: 'general' }">
    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-800">System Settings</h1>
        <button class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
            Save Changes
        </button>
    </div>

    <!-- Tabs -->
    <div class="border-b border-gray-200 mb-6">
        <nav class="-mb-px flex space-x-8">
            <button @click="activeTab = 'general'" 
                    :class="{ 'border-blue-500 text-blue-600': activeTab === 'general', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'general' }"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                General
            </button>
            <button @click="activeTab = 'mail'" 
                    :class="{ 'border-blue-500 text-blue-600': activeTab === 'mail', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'mail' }"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                Mail Configuration
            </button>
            <button @click="activeTab = 'payment'" 
                    :class="{ 'border-blue-500 text-blue-600': activeTab === 'payment', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'payment' }"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                Payment Gateways
            </button>
        </nav>
    </div>

    <!-- General Settings -->
    <div x-show="activeTab === 'general'" class="space-y-6">
        <div class="bg-white shadow px-4 py-5 sm:rounded-lg sm:p-6">
            <div class="md:grid md:grid-cols-3 md:gap-6">
                <div class="md:col-span-1">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">Platform Identity</h3>
                    <p class="mt-1 text-sm text-gray-500">Basic information about this installation.</p>
                </div>
                <div class="mt-5 md:mt-0 md:col-span-2">
                    <div class="grid grid-cols-6 gap-6">
                        <div class="col-span-6 sm:col-span-4">
                            <label for="platform_name" class="block text-sm font-medium text-gray-700">Platform Name</label>
                            <input type="text" name="platform_name" id="platform_name" value="<?= esc($settings['general']['platform_name']) ?>" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>
                        <div class="col-span-6 sm:col-span-4">
                            <label for="support_email" class="block text-sm font-medium text-gray-700">Support Email</label>
                            <input type="email" name="support_email" id="support_email" value="<?= esc($settings['general']['support_email']) ?>" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mail Settings -->
    <div x-show="activeTab === 'mail'" class="space-y-6" style="display: none;">
        <div class="bg-white shadow px-4 py-5 sm:rounded-lg sm:p-6">
            <div class="md:grid md:grid-cols-3 md:gap-6">
                <div class="md:col-span-1">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">SMTP Configuration</h3>
                    <p class="mt-1 text-sm text-gray-500">Configure how the system sends emails.</p>
                </div>
                <div class="mt-5 md:mt-0 md:col-span-2">
                    <div class="grid grid-cols-6 gap-6">
                        <div class="col-span-6 sm:col-span-3">
                            <label for="mail_host" class="block text-sm font-medium text-gray-700">SMTP Host</label>
                            <input type="text" name="mail_host" id="mail_host" value="<?= esc($settings['mail']['host']) ?>" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>
                        <div class="col-span-6 sm:col-span-3">
                            <label for="mail_port" class="block text-sm font-medium text-gray-700">Port</label>
                            <input type="number" name="mail_port" id="mail_port" value="<?= esc($settings['mail']['port']) ?>" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>
                        <div class="col-span-6 sm:col-span-3">
                            <label for="mail_username" class="block text-sm font-medium text-gray-700">Username</label>
                            <input type="text" name="mail_username" id="mail_username" value="<?= esc($settings['mail']['username']) ?>" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>
                        <div class="col-span-6 sm:col-span-3">
                            <label for="mail_password" class="block text-sm font-medium text-gray-700">Password</label>
                            <input type="password" name="mail_password" id="mail_password" value="<?= esc($settings['mail']['password']) ?>" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Settings -->
    <div x-show="activeTab === 'payment'" class="space-y-6" style="display: none;">
        <div class="bg-white shadow px-4 py-5 sm:rounded-lg sm:p-6">
            <div class="md:grid md:grid-cols-3 md:gap-6">
                <div class="md:col-span-1">
                    <h3 class="text-lg font-medium leading-6 text-gray-900">Payment Gateways</h3>
                    <p class="mt-1 text-sm text-gray-500">Manage API keys for payment providers.</p>
                </div>
                <div class="mt-5 md:mt-0 md:col-span-2">
                    <div class="space-y-6">
                        <div>
                            <h4 class="text-md font-medium text-gray-900">Pesapal</h4>
                            <div class="mt-4 grid grid-cols-6 gap-6">
                                <div class="col-span-6">
                                    <label for="pesapal_key" class="block text-sm font-medium text-gray-700">Consumer Key</label>
                                    <input type="text" name="pesapal_key" id="pesapal_key" value="<?= esc($settings['payment']['pesapal_key']) ?>" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                </div>
                                <div class="col-span-6">
                                    <label for="pesapal_secret" class="block text-sm font-medium text-gray-700">Consumer Secret</label>
                                    <input type="password" name="pesapal_secret" id="pesapal_secret" value="<?= esc($settings['payment']['pesapal_secret']) ?>" class="mt-1 focus:ring-blue-500 focus:border-blue-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
