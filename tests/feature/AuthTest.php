<?php

namespace Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use User\Models\UserModel;

class AuthTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resetServices();

        // Create a test user
        $userModel = new UserModel();
        $userModel->insert([
            'username' => 'testadmin',
            'email' => 'testadmin@kayacms.local',
            'password_hash' => password_hash('test123', PASSWORD_DEFAULT),
            'role_id' => 1,
            'status' => 'active',
        ]);
    }

    public function testAdminLoginPageLoads(): void
    {
        $result = $this->get('/admin/login');

        $result->assertOK();
        $result->assertSee('KayaCMS');
    }

    public function testAdminLoginWithValidCredentials(): void
    {
        $result = $this->post('/admin/auth/attempt', [
            'email' => 'testadmin@kayacms.local',
            'password' => 'test123',
        ]);

        $result->assertRedirectTo('/admin/dashboard');
    }

    public function testAdminLoginWithInvalidCredentials(): void
    {
        $result = $this->post('/admin/auth/attempt', [
            'email' => 'testadmin@kayacms.local',
            'password' => 'wrongpassword',
        ]);

        $result->assertRedirectTo('/admin/login');
    }
}
