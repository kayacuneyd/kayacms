<?php

namespace User\Controllers\Api;

use App\Core\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use User\Libraries\Mailer;
use User\Models\PasswordResetModel;
use User\Models\UserModel;

class PasswordResetController extends BaseController
{
    protected UserModel $userModel;
    protected PasswordResetModel $resetModel;

    public function __construct()
    {
        $this->userModel  = new UserModel();
        $this->resetModel = new PasswordResetModel();
    }

    /**
     * Request a password reset token (public).
     */
    public function request(): ResponseInterface
    {
        $rules = [
            'email' => 'required|valid_email',
        ];

        if (! $this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $email = $this->request->getJsonVar('email') ?? $this->request->getPost('email');

        // Always respond successfully to avoid user enumeration.
        $user = $this->userModel->findByEmail($email);

        if ($user && $user->status === 'active') {
            $token    = bin2hex(random_bytes(32));
            $expires  = date('Y-m-d H:i:s', strtotime('+30 minutes'));

            $this->resetModel->deleteForEmail($email);
            $this->resetModel->insert([
                'email'      => $email,
                'token'      => $token,
                'expires_at' => $expires,
            ]);

            $siteName = (new \Setting\Models\SettingModel())->getSetting('site_name', 'KayaCMS');
            $link     = base_url('admin/reset-password/' . $token);

            (new Mailer())->sendView(
                $email,
                'Reset your ' . $siteName . ' password',
                'emails/password_reset',
                ['token' => $token, 'siteName' => $siteName]
            );
        }

        if ($this->request->getHeaderLine('Content-Type') === 'application/x-www-form-urlencoded') {
            return $this->response
                ->setStatusCode(200)
                ->setJSON([
                    'message' => 'If your email is registered, a reset link has been sent.',
                    'redirect' => '/forgot-password?sent=1',
                    'success' => true,
                ]);
        }

        return $this->respond([
            'message' => 'If your email is registered, a reset link has been sent.',
        ]);
    }

    /**
     * Validate a token (public).
     */
    public function validateToken(string $token): ResponseInterface
    {
        $reset = $this->resetModel->validToken($token);

        if (! $reset) {
            return $this->failNotFound('Invalid or expired reset token.');
        }

        return $this->respond(['valid' => true]);
    }

    /**
     * Confirm a reset token and set a new password (public).
     */
    public function confirm(string $token): ResponseInterface
    {
        $rules = [
            'password' => 'required|min_length[6]',
        ];

        if (! $this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $reset = $this->resetModel->validToken($token);

        if (! $reset) {
            return $this->fail('Invalid or expired reset token.', 400);
        }

        $user = $this->userModel->findByEmail($reset['email']);

        if (! $user) {
            return $this->failNotFound('User not found.');
        }

        $this->userModel->update($user->id, [
            'password' => $this->request->getJsonVar('password'),
        ]);

        $this->resetModel->where('email', $reset['email'])->delete();

        \User\Libraries\ActivityLog::auth('password_reset', (int) $user->id, $user->username, 'Password reset completed');

        return $this->respond(['message' => 'Password updated successfully.']);
    }
}