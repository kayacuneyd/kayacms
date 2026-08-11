<?php

namespace Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Theme\Models\ThemeModel;
use User\Models\RoleModel;
use User\Models\UserModel;

class CkeditorPackagingTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resetServices();

        $db = \Config\Database::connect();
        $db->table('roles')->truncate();
        $db->table('users')->where('1=1')->delete();
        $db->table('themes')->where('1=1')->delete();

        $roleModel = new RoleModel();
        $roleModel->insert(['name' => 'admin', 'permissions' => '["*"]']);
        $roleId = (int) $roleModel->getInsertID();

        (new UserModel())->insert([
            'username' => 'ckadmin',
            'email' => 'ck@kayacms.local',
            'password_hash' => password_hash('test123', PASSWORD_DEFAULT),
            'role_id' => $roleId,
            'status' => 'active',
        ]);

        (new ThemeModel())->insert([
            'name' => 'Default',
            'slug' => 'default',
            'is_active' => 1,
        ]);
    }

    private function login(): void
    {
        $this->post('/admin/auth/attempt', [
            'email' => 'ck@kayacms.local',
            'password' => 'test123',
        ]);
        $this->session = $_SESSION;
    }

    public function testContentFormLoadsEditorFromLocalVendorAsset(): void
    {
        $this->login();

        $result = $this->get('/admin/content/create');
        $result->assertOK();

        $this->assertStringContainsString(
            'assets/vendor/ckeditor/ckeditor.js',
            $result->getBody()
        );
    }

    public function testContentFormDoesNotReferenceExternalCdn(): void
    {
        $this->login();

        $result = $this->get('/admin/content/create');
        $result->assertOK();

        $this->assertStringNotContainsString(
            'cdn.ckeditor.com',
            $result->getBody()
        );
    }

    public function testLocalCkeditorBundleExists(): void
    {
        $path = ROOTPATH . 'public/assets/vendor/ckeditor/ckeditor.js';

        $this->assertFileExists($path);
        $this->assertGreaterThan(100000, filesize($path));
    }
}
