<?php

namespace Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use App\Libraries\QueryCache;
use Setting\Models\SettingModel;
use Theme\Models\ThemeModel;
use User\Models\RoleModel;
use User\Models\UserModel;

class ScriptsInjectionTest extends CIUnitTestCase
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
        QueryCache::instance()->forget('theme');

        $roleModel = new RoleModel();
        $roleModel->insert(['name' => 'admin', 'permissions' => '["*"]']);
        $roleId = (int) $roleModel->getInsertID();

        $userModel = new UserModel();
        $userModel->insert([
            'username' => 'scriptsadmin',
            'email' => 'scripts@kayacms.local',
            'password_hash' => password_hash('test123', PASSWORD_DEFAULT),
            'role_id' => $roleId,
            'status' => 'active',
        ]);

        (new ThemeModel())->insert([
            'name' => 'Default',
            'slug' => 'default',
            'is_active' => 1,
        ]);

        $settingModel = new SettingModel();
        $settingModel->setSetting('header_scripts', '<meta name="custom-header" content="injected">');
        $settingModel->setSetting('footer_scripts', '<script id="footer-inject">window.__injected = true;</script>');
    }

    private function login(): void
    {
        $this->post('/admin/auth/attempt', [
            'email' => 'scripts@kayacms.local',
            'password' => 'test123',
        ]);
        $this->session = $_SESSION;
    }

    public function testHeaderScriptsRenderInHead(): void
    {
        $result = $this->get('/');
        $result->assertOK();
        $this->assertStringContainsString(
            '<meta name="custom-header" content="injected">',
            $result->getBody()
        );
    }

    public function testFooterScriptsRenderBeforeBodyClose(): void
    {
        $result = $this->get('/');
        $result->assertOK();
        $this->assertStringContainsString(
            '<script id="footer-inject">window.__injected = true;</script>',
            $result->getBody()
        );
    }

    public function testAdminCanUpdateScriptSettings(): void
    {
        $this->login();

        $setting = (new SettingModel())->where('key', 'header_scripts')->first();

        $result = $this->post('/admin/settings/update/header_scripts', [
            'value' => '<script>console.log("updated")</script>',
        ]);
        $result->assertRedirectTo('/admin/settings');
        $result->assertSessionHas('success');

        $this->assertSame(
            '<script>console.log("updated")</script>',
            (new SettingModel())->getSetting('header_scripts', '')
        );
    }

    public function testHeaderScriptsSurviveEscaping(): void
    {
        $this->login();

        // update first so the frontend sees it directly
        $this->post('/admin/settings/update/header_scripts', [
            'value' => '<meta name="seo-verify" content="abc123">',
        ]);

        $result = $this->get('/');
        $result->assertOK();
        $this->assertStringContainsString(
            '<meta name="seo-verify" content="abc123">',
            $result->getBody()
        );
    }
}