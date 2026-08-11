<?php
namespace User\Controllers\Api;

use App\Core\BaseController;
use User\Models\UserModel;
use User\Libraries\JWTLib;
use CodeIgniter\HTTP\ResponseInterface;

class AuthController extends BaseController
{
    protected UserModel $userModel;
    protected JWTLib $jwt;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->jwt = new JWTLib();
    }

    /**
     * User login - returns JWT token
     */
    public function login(): ResponseInterface
    {
        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required|min_length[6]',
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $email = $this->request->getJsonVar('email');
        $password = $this->request->getJsonVar('password');

        $user = $this->userModel->findByEmail($email);

        if (!$user || !$user->verifyPassword($password)) {
            return $this->failUnauthorized('Invalid email or password');
        }

        if ($user->status !== 'active') {
            return $this->failForbidden('Account is not active');
        }

        $token = $this->jwt->createToken($user->id, $user->username, $user->role_id);

        return $this->respond([
            'message' => 'Login successful',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'role_id' => $user->role_id,
            ]
        ]);
    }

    /**
     * User registration
     */
    public function register(): ResponseInterface
    {
        $rules = [
            'username' => 'required|min_length[3]|max_length[100]|is_unique[cms_users.username]',
            'email'    => 'required|valid_email|is_unique[cms_users.email]',
            'password' => 'required|min_length[6]',
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $data = [
            'username' => $this->request->getJsonVar('username'),
            'email'    => $this->request->getJsonVar('email'),
            'password' => $this->request->getJsonVar('password'),
            'status'   => 'active',
        ];

        if (!$this->userModel->insert($data)) {
            return $this->failResponse('Registration failed', 500, $this->userModel->errors());
        }

        $userId = $this->userModel->getInsertID();
        $token = $this->jwt->createToken($userId, $data['username'], null);

        return $this->respondCreated([
            'message' => 'Registration successful',
            'token' => $token,
            'user' => [
                'id' => $userId,
                'username' => $data['username'],
                'email' => $data['email'],
            ]
        ]);
    }

    /**
     * Get current user info (requires auth)
     */
    public function me(): ResponseInterface
    {
        $user = $this->userModel->withRole()->find($this->request->user->id);

        if (!$user) {
            return $this->failNotFound('User not found');
        }

        return $this->respond([
            'user' => $user
        ]);
    }

    /**
     * Refresh token (requires auth)
     */
    public function refresh(): ResponseInterface
    {
        $user = $this->request->user;

        $token = $this->jwt->createToken($user->id, $user->username, $user->role_id);

        return $this->respond([
            'message' => 'Token refreshed',
            'token' => $token
        ]);
    }

    /**
     * Admin login with session
     */
    public function adminLogin(): ResponseInterface
    {
        $rules = [
            'username' => 'required',
            'password' => 'required',
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        $user = $this->userModel->findByUsername($username);

        if (!$user || !$user->verifyPassword($password)) {
            return $this->failUnauthorized('Invalid credentials');
        }

        if ($user->status !== 'active') {
            return $this->failForbidden('Account is not active');
        }

        // Create session
        $session = session();
        $session->set([
            'user_id' => $user->id,
            'username' => $user->username,
            'role_id' => $user->role_id,
            'logged_in' => true,
        ]);

        return $this->respond([
            'message' => 'Login successful',
            'redirect' => '/admin/dashboard'
        ]);
    }

    /**
     * Admin logout
     */
    public function adminLogout(): ResponseInterface
    {
        session()->destroy();

        return $this->respond([
            'message' => 'Logged out successfully',
            'redirect' => '/admin/login'
        ]);
    }

    /**
     * Forgot password page (server-rendered).
     */
    public function forgot()
    {
        return view('user/forgot_password', ['title' => 'Forgot Password']);
    }

    /**
     * Reset password form (server-rendered).
     */
    public function resetForm(string $token)
    {
        $resetModel = new \User\Models\PasswordResetModel();

        if (! $resetModel->validToken($token)) {
            return redirect()->to('/admin/login')->with('error', 'Invalid or expired reset token.');
        }

        return view('user/password_reset', ['token' => $token, 'title' => 'Reset Password']);
    }
}
