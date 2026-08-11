<?php

namespace Setting\Controllers\Admin;

use App\Controllers\Admin\BaseAdminController;
use Setting\Models\SettingModel;

class SettingAdminController extends BaseAdminController
{
    protected SettingModel $settingModel;

    public function __construct()
    {
        $this->settingModel = new SettingModel();
    }

    public function index()
    {
        if ($redirect = $this->requirePermission('settings.view')) return $redirect;

        $data['active'] = 'settings';
        $data['title']  = 'Settings';
        $data['items']  = $this->settingModel->findAll();
        $data['canUpdate'] = $this->can('settings.update');

        return $this->render('admin/settings/index', $data);
    }

    public function update(string $key)
    {
        if ($redirect = $this->requirePermission('settings.update')) return $redirect;

        $setting = $this->settingModel->where('key', $key)->first();

        if (! $setting || empty($setting['id'])) {
            return redirect()->to('/admin/settings')->with('error', 'Setting not found.');
        }

        $this->settingModel->update($setting['id'], [
            'value' => $this->request->getPost('value'),
        ]);

        $this->logActivity('updated', 'setting', (int) $setting['id'], "Updated setting: {$key}");

        return redirect()->to('/admin/settings')->with('success', 'Setting updated successfully.');
    }

    public function bulkUpdate()
    {
        if ($redirect = $this->requirePermission('settings.update')) return $redirect;

        $settings = $this->request->getPost('settings');

        foreach ($settings ?? [] as $key => $value) {
            $this->settingModel->setSetting($key, $value);
        }

        return redirect()->to('/admin/settings')->with('success', 'Settings saved successfully.');
    }

    /**
     * Send a test email using the current SMTP settings.
     */
    public function testEmail()
    {
        if ($redirect = $this->requirePermission('settings.update')) return $redirect;

        $userId = (int) session()->get('user_id');
        $user   = $userId ? (new \User\Models\UserModel())->find($userId) : null;

        if (! $user) {
            return redirect()->back()->with('error', 'No recipient found for test email.');
        }

        $mailer = new \User\Libraries\Mailer();
        $sent = $mailer->sendView(
            $user->email,
            'Test email from ' . $this->settingModel->getSetting('site_name', 'KayaCMS'),
            'emails/test_mail',
            ['siteName' => $this->settingModel->getSetting('site_name', 'KayaCMS')]
        );

        if ($sent) {
            $this->logActivity('sent', 'email', null, "Test email sent to {$user->email}");

            return redirect()->to('/admin/settings')->with('success', 'Test email sent to ' . $user->email . '.');
        }

        return redirect()->to('/admin/settings')->with('error', 'Test email could not be sent. Check SMTP settings.');
    }
}
