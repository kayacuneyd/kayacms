<?php

namespace Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use User\Models\RoleModel;
use User\Models\UserModel;

class MediaTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resetServices();

        $roleModel = new RoleModel();
        $db = \Config\Database::connect();
        $db->table('roles')->truncate();
        $db->table('users')->where('1=1')->delete();
        $roleModel->insert([
            'name'        => 'admin',
            'permissions' => '["*"]',
        ]);
        $roleId = (int) $roleModel->getInsertID();

        $userModel = new UserModel();
        $userModel->insert([
            'username' => 'mediaman',
            'email' => 'media@kayacms.local',
            'password_hash' => password_hash('test123', PASSWORD_DEFAULT),
            'role_id' => $roleId,
            'status' => 'active',
        ]);
    }

    public function testMediaAdminRequiresLogin(): void
    {
        $result = $this->get('/admin/media');
        $result->assertRedirectTo('/admin/login');
    }

    public function testUploadPageLoadsWhenLoggedIn(): void
    {
        $this->post('/admin/auth/attempt', [
            'email' => 'media@kayacms.local',
            'password' => 'test123',
        ]);
        $this->session = $_SESSION;

        $result = $this->get('/admin/media/upload');
        $result->assertOK();
        $result->assertSee('Upload Media');
    }
}