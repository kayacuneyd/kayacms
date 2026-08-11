<?php

namespace Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use App\Libraries\QueryCache;
use Theme\Libraries\ThemeConfig;
use Theme\Models\ThemeModel;
use User\Models\RoleModel;
use User\Models\UserModel;

class ThemeConfigTest extends CIUnitTestCase
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

        (new UserModel())->insert([
            'username' => 'themeadmin',
            'email' => 'theme@kayacms.local',
            'password_hash' => password_hash('test123', PASSWORD_DEFAULT),
            'role_id' => $roleId,
            'status' => 'active',
        ]);
    }

    private function login(): void
    {
        $this->post('/admin/auth/attempt', [
            'email' => 'theme@kayacms.local',
            'password' => 'test123',
        ]);
        $this->session = $_SESSION;
    }

    public function testSchemaLoadsFromThemeConfigFile(): void
    {
        $config = new ThemeConfig();
        $schema = $config->schema('default');

        $this->assertNotEmpty($schema);

        $keys = array_column($schema, 'key');
        $this->assertContains('brand_color', $keys);
        $this->assertContains('show_search', $keys);
        $this->assertContains('footer_text', $keys);
        $this->assertContains('header_layout', $keys);
    }

    public function testSchemaEmptyForUnknownTheme(): void
    {
        $config = new ThemeConfig();
        $this->assertSame([], $config->schema('does-not-exist'));
    }

    public function testResolveMergesDefaults(): void
    {
        $themeId = (new ThemeModel())->insert([
            'name' => 'Default',
            'slug' => 'default',
            'is_active' => 1,
        ]);

        $resolved = (new ThemeConfig())->resolve(['id' => $themeId, 'slug' => 'default', 'config' => '{}']);

        $this->assertSame('#2563eb', $resolved['brand_color']);
        $this->assertSame('1', $resolved['show_search']);
    }

    public function testSaveOnlyPersistsKnownKeys(): void
    {
        $themeModel = new ThemeModel();
        $themeId = (int) $themeModel->insert([
            'name' => 'Default',
            'slug' => 'default',
            'is_active' => 1,
        ]);

        $config = new ThemeConfig();
        $saved = $config->save($themeId, [
            'brand_color' => '#ff0000',
            'footer_text' => 'Hello world',
            'evil_key'    => 'nope',
            'show_search' => '0',
        ]);

        $this->assertTrue($saved);

        $theme = $themeModel->find($themeId);
        $savedData = json_decode($theme['config'], true);

        $this->assertSame('#ff0000', $savedData['brand_color']);
        $this->assertSame('0', $savedData['show_search']);
        $this->assertSame('Hello world', $savedData['footer_text']);
        $this->assertArrayNotHasKey('evil_key', $savedData);

        $resolved = (new ThemeConfig())->resolve($theme);
        $this->assertSame('#ff0000', $resolved['brand_color']);
        $this->assertSame('0', $resolved['show_search']);
    }

    public function testAdminConfigPageRendersFields(): void
    {
        $this->login();

        $themeModel = new ThemeModel();
        $themeId = (int) $themeModel->insert([
            'name' => 'Default',
            'slug' => 'default',
            'is_active' => 1,
        ]);

        $result = $this->get("/admin/themes/config/{$themeId}");
        $result->assertOK();
        $result->assertSee('Configure Default');
        $result->assertSee('Brand Color');
        $result->assertSee('Show Search Bar');
    }

    public function testAdminSavesThemeConfig(): void
    {
        $this->login();

        $themeModel = new ThemeModel();
        $themeId = (int) $themeModel->insert([
            'name' => 'Default',
            'slug' => 'default',
            'is_active' => 1,
        ]);

        $result = $this->post("/admin/themes/config/{$themeId}", [
            'config' => [
                'brand_color' => '#123456',
                'show_search' => '0',
                'footer_text' => 'Powered by KayaCMS',
            ],
        ]);

        $result->assertRedirectTo('/admin/themes');
        $result->assertSessionHas('success');

        $theme = $themeModel->find($themeId);
        $savedData = json_decode($theme['config'], true);
        $this->assertSame('#123456', $savedData['brand_color']);
        $this->assertSame('0', $savedData['show_search']);
        $this->assertSame('Powered by KayaCMS', $savedData['footer_text']);
    }

    public function testFrontendExposesThemeConfig(): void
    {
        $this->login();

        $themeModel = new ThemeModel();
        $themeId = (int) $themeModel->insert([
            'name' => 'Default',
            'slug' => 'default',
            'is_active' => 1,
        ]);
        $this->post("/admin/themes/config/{$themeId}", [
            'config' => [
                'brand_color' => '#0a0b0c',
                'footer_text' => 'Custom Footer Text',
            ],
        ]);

        $result = $this->get('/');
        $result->assertOK();
        $result->assertSee('Custom Footer Text');
        $result->assertSee('#0a0b0c');
    }
}