<?php

namespace Modules\Foundation\Controllers;

use App\Controllers\BaseController;
use Modules\Foundation\Services\SettingsService;

class SettingsController extends BaseController
{
    protected $settings;

    public function __construct()
    {
        $this->settings = new SettingsService();
    }

    public function index()
    {
        // Fetch real settings from DB, falling back to defaults if empty
        $data = [
            'settings' => [
                'general' => [
                    'platform_name' => $this->settings->get('general', 'platform_name', 'ShuleLabs'),
                    'support_email' => $this->settings->get('general', 'support_email', ''),
                ],
                'mail' => [
                    'host' => $this->settings->get('mail', 'host', ''),
                    'port' => $this->settings->get('mail', 'port', 587),
                    'username' => $this->settings->get('mail', 'username', ''),
                    'password' => $this->settings->get('mail', 'password', ''),
                ],
                'payment' => [
                    'pesapal_key' => $this->settings->get('payment', 'pesapal_key', ''),
                    'pesapal_secret' => $this->settings->get('payment', 'pesapal_secret', ''),
                ],
            ],
            'activeTab' => 'general',
        ];

        return view('Modules\Foundation\Views\settings\index', $data);
    }

    public function update()
    {
        $post = $this->request->getPost();

        // General Settings
        if (isset($post['platform_name'])) {
            $this->settings->set('general', 'platform_name', $post['platform_name']);
            $this->settings->set('general', 'support_email', $post['support_email']);
        }

        // Mail Settings
        if (isset($post['mail_host'])) {
            $this->settings->set('mail', 'host', $post['mail_host']);
            $this->settings->set('mail', 'port', $post['mail_port'], 'integer');
            $this->settings->set('mail', 'username', $post['mail_username']);
            $this->settings->set('mail', 'password', $post['mail_password']);
        }

        // Payment Settings
        if (isset($post['pesapal_key'])) {
            $this->settings->set('payment', 'pesapal_key', $post['pesapal_key']);
            $this->settings->set('payment', 'pesapal_secret', $post['pesapal_secret']);
        }

        return redirect()->back()->with('success', 'Settings updated successfully.');
    }
}
