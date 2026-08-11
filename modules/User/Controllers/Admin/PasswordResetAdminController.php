<?php

namespace User\Controllers\Admin;

use App\Controllers\Admin\BaseAdminController;
use User\Models\PasswordResetModel;
use User\Models\UserModel;

class PasswordResetAdminController extends BaseAdminController
{
    protected UserModel $userModel;
    protected PasswordResetModel $resetModel;

    public function __construct()
    {
        $this->userModel  = new UserModel();
        $this->resetModel = new PasswordResetModel();
    }

    public function show(string $token)
    {
        $this->guardToken($token);

        return view('user/password_reset', ['token' => $token, 'title' => 'Reset Password']);
    }

    public function reset(string $token)
    {
        $reset = $this->resetModel->validToken($token);

        if (! $reset) {
            return redirect()->to('/admin/login')->with('error', 'Invalid or expired reset token.');
        }

        $rules = [
            'password'     => 'required|min_length[6]',
            'confirmation' => 'required|matches[password]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode(', ', $this->validator->getErrors()));
        }

        $user = $this->userModel->findByEmail($reset['email']);

        if (! $user) {
            return redirect()->to('/admin/login')->with('error', 'User not found.');
        }

        $this->userModel->update($user->id, [
            'password' => $this->request->getPost('password'),
        ]);

        $this->resetModel->deleteForEmail($reset['email']);

        \User\Libraries\ActivityLog::auth('password_reset', (int) $user->id, $user->username, 'Password reset via admin link');

        return redirect()->to('/admin/login')->with('success', 'Password updated. You can now login.');
    }

    private function guardToken(string $token)
    {
        if (! $this->resetModel->validToken($token)) {
            return redirect()->to('/admin/login')->with('error', 'Invalid or expired reset token.');
        }

        return null;
    }
}