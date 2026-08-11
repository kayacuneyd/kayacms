<?php

namespace App\Controllers\Admin;

use App\Controllers\Admin\BaseAdminController;
use User\Models\UserModel;

class AuthController extends BaseAdminController
{
    protected UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function login()
    {
        if (session()->has('user_id')) {
            return redirect()->to('/admin/dashboard');
        }

        return view('admin/login');
    }

    public function logout()
    {
        $userId = session()->get('user_id');
        $username = session()->get('username');

        session()->destroy();

        if ($userId) {
            \User\Libraries\ActivityLog::auth('logout', $userId, $username, 'User logged out from admin panel');
        }

        return redirect()->to('/admin/login')->with('success', 'Logged out successfully.');
    }

    public function attempt()
    {
        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required|min_length[6]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode(', ', $this->validator->getErrors()));
        }

        $email    = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        $throttle = new \User\Libraries\LoginThrottle();

        if ($throttle->isBlocked($email)) {
            \User\Libraries\SecurityLog::warning('login_locked', "Too many failed attempts for {$email}", null, ['email' => $email]);

            return redirect()->to('/admin/login')->withInput()
                ->with('error', 'Too many failed login attempts. Please try again after the lockout period.');
        }

        $user = $this->userModel->where('email', $email)->first();

        if (! $user || ! password_verify($password, $user->password_hash)) {
            $throttle->record($email, false);
            \User\Libraries\SecurityLog::warning('login_failed', "Failed login attempt for {$email}");

            return redirect()->to('/admin/login')->withInput()->with('error', 'Invalid credentials');
        }

        if ($user->status !== 'active') {
            $throttle->record($email, false);
            \User\Libraries\SecurityLog::warning('login_disabled', 'Login attempted on disabled account', $user->id);

            return redirect()->to('/admin/login')->withInput()->with('error', 'Account is not active');
        }

        // TOTP two-factor challenge
        $totp = new \User\Libraries\TwoFactorAuth();
        if ($user->totp_secret && ! session()->get('totp_verified')) {
            $totp->startChallenge($user->id);

            return redirect()->to('/admin/auth/totp')->with('success', 'Enter your verification code');
        }

        session()->set([
            'user_id'  => $user->id,
            'username' => $user->username,
            'role_id'  => $user->role_id,
            'logged_in' => true,
        ]);

        $throttle->record($email, true);
        \User\Libraries\SecurityLog::info('login_success', 'Successful login from admin panel', $user->id);
        \User\Libraries\ActivityLog::auth('login', $user->id, $user->username, 'User logged in via admin panel');

        return redirect()->to('/admin/dashboard')->with('success', 'Login successful');
    }

    /**
     * Passwordless sign-in: show the "request a magic link" form.
     */
    public function magicLinkForm()
    {
        if (session()->has('user_id')) {
            return redirect()->to('/admin/dashboard');
        }

        $magic = new \User\Libraries\MagicLink();

        if (! $magic->isEnabled()) {
            return redirect()->to('/admin/login')->with('error', 'Passwordless sign-in is disabled.');
        }

        return view('admin/magic_link');
    }

    /**
     * Passwordless sign-in: send a one-time login link by email.
     */
    public function magicLinkRequest()
    {
        $rules = [
            'email' => 'required|valid_email',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode(', ', $this->validator->getErrors()));
        }

        $email = $this->request->getPost('email');
        $magic = new \User\Libraries\MagicLink();

        if (! $magic->isEnabled()) {
            return redirect()->to('/admin/login')->with('error', 'Passwordless sign-in is disabled.');
        }

        $throttle = new \User\Libraries\LoginThrottle();

        if ($throttle->isBlocked($email)) {
            \User\Libraries\SecurityLog::warning('magic_link_locked', "Too many magic link requests for {$email}", null, ['email' => $email]);

            return redirect()->back()->withInput()
                ->with('error', 'Too many requests. Please try again later.');
        }

        $user = $this->userModel->where('email', $email)->first();

        // Respond identically whether or not the user exists (anti-enumeration).
        if ($user && $user->status === 'active') {
            $token = $magic->issue((int) $user->id);
            $link  = $magic->linkFor($token);

            $siteName = (new \Setting\Models\SettingModel())->getSetting('site_name', 'KayaCMS');

            (new \User\Libraries\Mailer())->sendView(
                $email,
                'Sign in to ' . $siteName,
                'emails/magic_link',
                ['link' => $link, 'siteName' => $siteName]
            );

            \User\Libraries\SecurityLog::info('magic_link_sent', "Magic link sent for {$email}", (int) $user->id, ['email' => $email]);
        }

        return redirect()->to('/admin/magic-link')
            ->with('success', 'If that email is registered, a sign-in link has been sent. Check your inbox.');
    }

    /**
     * Passwordless sign-in: consume the one-time link and establish a session.
     */
    public function magicLinkConsume(string $token)
    {
        $magic = new \User\Libraries\MagicLink();
        $user  = $magic->consume($token);

        if ($user === null) {
            return redirect()->to('/admin/login')->with('error', 'This sign-in link is invalid or has expired.');
        }

        if ($user->status !== 'active') {
            return redirect()->to('/admin/login')->with('error', 'Account is not active');
        }

        // TOTP two-factor challenge
        $totp = new \User\Libraries\TwoFactorAuth();
        if ($user->totp_secret && ! session()->get('totp_verified')) {
            $totp->startChallenge((int) $user->id);

            return redirect()->to('/admin/auth/totp')->with('success', 'Enter your verification code');
        }

        session()->set([
            'user_id'   => $user->id,
            'username'  => $user->username,
            'role_id'   => $user->role_id,
            'logged_in' => true,
        ]);

        \User\Libraries\SecurityLog::info('magic_link_login', 'Logged in via magic link', (int) $user->id);
        \User\Libraries\ActivityLog::auth('login', (int) $user->id, $user->username, 'User logged in via passwordless link');

        return redirect()->to('/admin/dashboard')->with('success', 'Login successful');
    }

    /**
     * TOTP verification page shown when a 2FA-enabled user logs in.
     */
    public function totpForm()
    {
        if (session()->get('totp_verified')) {
            return redirect()->to('/admin/dashboard');
        }

        $userId = session()->get('totp_pending_user');

        if (! $userId) {
            return redirect()->to('/admin/login');
        }

        return view('admin/totp');
    }

    public function totpVerify()
    {
        $userId = session()->get('totp_pending_user');
        $username = session()->get('username');

        if (! $userId) {
            return redirect()->to('/admin/login');
        }

        $rules = ['code' => 'required|numeric|exact_length[6]'];

        if (! $this->validate($rules)) {
            return redirect()->back()->with('error', 'Enter the 6-digit code from your authenticator app.');
        }

        $user = $this->userModel->find($userId);

        if (! $user || ! $user->totp_secret) {
            return redirect()->to('/admin/login')->with('error', 'Sign-in session expired. Please login again.');
        }

        $two = new \User\Libraries\TwoFactorAuth();

        if (! $two->verify($user->totp_secret, $this->request->getPost('code'))) {
            \User\Libraries\SecurityLog::warning('totp_failed', 'Invalid two-factor authentication code', $user->id);

            return redirect()->back()->with('error', 'Invalid authentication code.');
        }

        session()->set('totp_verified', true);

        \User\Libraries\SecurityLog::info('totp_success', 'Two-factor authentication verified', $user->id);

        return redirect()->to('/admin/dashboard')->with('success', 'Login successful');
    }
}

