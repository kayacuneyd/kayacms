<?php

namespace App\Controllers\Admin;

use User\Libraries\LoginThrottle;
use User\Libraries\SecurityLog;
use User\Libraries\TwoFactorAuth;
use User\Models\SecurityLogModel;
use User\Models\UserModel;

class SecurityController extends BaseAdminController
{
    /**
     * Security overview dashboard with lockout status + recent logs.
     */
    public function index()
    {
        if ($redirect = $this->requirePermission('security.view')) {
            return $redirect;
        }

        $logModel = new SecurityLogModel();

        $this->data['logs'] = $logModel->recent(100);
        $this->data['critical24h'] = $logModel->countBySeverity('critical', date('Y-m-d H:i:s', time() - 86400));
        $this->data['warning24h'] = $logModel->countBySeverity('warning', date('Y-m-d H:i:s', time() - 86400));
        $this->data['info24h'] = $logModel->countBySeverity('info', date('Y-m-d H:i:s', time() - 86400));
        $this->data['blockedIps'] = $logModel->where('severity', 'critical')
            ->where('created_at >=', date('Y-m-d H:i:s', time() - 86400))
            ->countAllResults();

        return $this->render('admin/security/index', $this->data);
    }

    /**
     * 2FA management for the current user (enable/setup/disable).
     */
    public function twoFactor()
    {
        if ($redirect = $this->requirePermission('security.view')) {
            return $redirect;
        }

        $user = (new \User\Models\UserModel())->find((int) session()->get('user_id'));

        $two = new TwoFactorAuth();
        $secret = $two->generateSecret();

        $this->data['user'] = $user;
        $this->data['secret'] = $secret;
        $this->data['provisioningUri'] = $user ? $two->provisioningUri($secret, $user->email) : '';

        return $this->render('admin/security/twofactor', $this->data);
    }

    /**
     * Store/verify a newly-generated TOTP secret for the current user.
     */
    public function twoFactorEnable()
    {
        $user = (new \User\Models\UserModel())->find((int) session()->get('user_id'));

        if (! $user) {
            return redirect()->to('/admin/login');
        }

        $rules = [
            'secret' => 'required',
            'code'   => 'required|numeric|exact_length[6]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->with('error', 'Invalid setup data.');
        }

        $two = new TwoFactorAuth();

        if (! $two->verify($this->request->getPost('secret'), $this->request->getPost('code'))) {
            return redirect()->back()->with('error', 'The verification code did not match. Please try again.');
        }

        (new \User\Models\UserModel())->update($user->id, ['totp_secret' => $this->request->getPost('secret')]);
        SecurityLog::info('totp_enabled', 'Two-factor authentication enabled', $user->id);
        \User\Libraries\Notifications::notify((int) $user->id, 'security', 'Two-factor authentication enabled', '2FA is now active on your account.');

        return redirect()->to('/admin/security')->with('success', 'Two-factor authentication enabled.');
    }

    /**
     * Disable 2FA for the current user.
     */
    public function twoFactorDisable()
    {
        $user = (new \User\Models\UserModel())->find((int) session()->get('user_id'));

        if (! $user) {
            return redirect()->to('/admin/login');
        }

        (new \User\Models\UserModel())->update($user->id, ['totp_secret' => null]);
        SecurityLog::log('totp_disabled', 'Two-factor authentication disabled', $user->id);

        return redirect()->to('/admin/security')->with('success', 'Two-factor authentication disabled.');
    }

    /**
     * Run a light security audit and surface recommendations.
     */
    public function audit()
    {
        if ($redirect = $this->requirePermission('security.view')) {
            return $redirect;
        }

        $issues = [];

        $settings = service('settings');
        $adminEmail = $settings->get('site.email') ?? 'admin@kayacms.local';

        $admin = (new \User\Models\UserModel())->where('email', $adminEmail)->first();
        if ($admin && ($admin->email === 'admin@kayacms.local')) {
            $issues[] = ['severity' => 'high', 'title' => 'Default admin email in use', 'detail' => 'Change the admin account email to a private one.'];
        }

        if (defined('ENVIRONMENT') && ENVIRONMENT === 'production') {
            $issues[] = ['severity' => 'info', 'title' => 'Environment check', 'detail' => 'The environment is set to production.'];
        }

        $recentFailures = (new \User\Models\LoginAttemptModel())
            ->where('success', 0)
            ->where('created_at >=', date('Y-m-d H:i:s', time() - 86400))
            ->countAllResults();

        if ($recentFailures > 0) {
            $issues[] = ['severity' => 'info', 'title' => 'Failed logins logged', 'detail' => "{$recentFailures} failed login attempt(s) in the last 24 hours."];
        }

        $this->data['issues'] = $issues;

        return $this->render('admin/security/audit', $this->data);
    }
}