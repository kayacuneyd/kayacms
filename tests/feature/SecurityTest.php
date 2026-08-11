<?php

namespace Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use User\Libraries\SecurityLog;
use User\Models\LoginAttemptModel;
use User\Models\RoleModel;
use User\Models\UserModel;
use User\Libraries\TwoFactorAuth;

class SecurityTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resetServices();

        $db = \Config\Database::connect();
        $db->table('roles')->truncate();
        $db->table('users')->where('1=1')->delete();
        $db->table('login_attempts')->where('1=1')->delete();
        $db->table('security_logs')->where('1=1')->delete();

        $roleModel = new RoleModel();
        $roleModel->insert(['name' => 'admin', 'permissions' => '["*"]']);
        $roleId = (int) $roleModel->getInsertID();

        (new UserModel())->insert([
            'username' => 'secuser',
            'email' => 'sec@kayacms.local',
            'password_hash' => password_hash('test123', PASSWORD_DEFAULT),
            'role_id' => $roleId,
            'status' => 'active',
        ]);
    }

    public function testSecurityPageRequiresLogin(): void
    {
        $result = $this->get('/admin/security');
        $result->assertRedirectTo('/admin/login');
    }

    public function testSecurityPageLoadsForLoggedInUser(): void
    {
        $this->post('/admin/auth/attempt', [
            'email' => 'sec@kayacms.local',
            'password' => 'test123',
        ]);
        $this->session = $_SESSION;

        SecurityLog::info('test', 'A test security event');

        $result = $this->get('/admin/security');
        $result->assertOK();
        $result->assertSee('Security Overview');
        $result->assertSee('test');
    }

    public function testFailedLoginIsLoggedAndBlocks(): void
    {
        $result = $this->post('/admin/auth/attempt', [
            'email' => 'sec@kayacms.local',
            'password' => 'wrongpass',
        ]);
        $result->assertRedirect();

        $count = (new LoginAttemptModel())
            ->where('email', 'sec@kayacms.local')
            ->where('success', 0)
            ->countAllResults();

        $this->assertSame(1, $count);

        $logs = \Config\Database::connect()->table('security_logs')
            ->where('type', 'login_failed')
            ->countAllResults();

        $this->assertSame(1, $logs);
    }

    public function testTwoFactorTotpVerify(): void
    {
        $two = new TwoFactorAuth();
        $secret = $two->generateSecret();
        $code = $two->generateCode($secret);

        $this->assertTrue($two->verify($secret, $code));

        $user = (new UserModel())->findByEmail('sec@kayacms.local');
        (new UserModel())->update($user->id, ['totp_secret' => $secret]);

        $this->post('/admin/auth/attempt', [
            'email' => 'sec@kayacms.local',
            'password' => 'test123',
        ]);
        $this->session = $_SESSION;

        $this->assertTrue($user->status === 'active');

        $result = $this->get('/admin/security/2fa');
        $this->assertNotEmpty((string) $result->getBody());
        // 2FA page should be reachable for logged in admin
        $result->assertOK();
    }
}