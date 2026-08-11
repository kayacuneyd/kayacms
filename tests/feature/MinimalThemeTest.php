<?php

namespace Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use App\Libraries\QueryCache;
use Theme\Libraries\ThemeConfig;
use Theme\Models\ThemeModel;
use User\Models\RoleModel;
use User\Models\UserModel;

class MinimalThemeTest extends CIUnitTestCase
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
        $db->table('content')->where('1=1')->delete();
        QueryCache::instance()->forget('theme');

        $roleModel = new RoleModel();
        $roleModel->insert(['name' => 'admin', 'permissions' => '["*"]']);
        $roleId = (int) $roleModel->getInsertID();

        $userModel = new UserModel();
        $userModel->insert([
            'username' => 'madmin',
            'email' => 'm@kayacms.local',
            'password_hash' => password_hash('test123', PASSWORD_DEFAULT),
            'role_id' => $roleId,
            'status' => 'active',
        ]);
        $this->authorId = (int) $userModel->findByEmail('m@kayacms.local')->id;

        $this->defaultTheme  = (int) (new ThemeModel())->insert([
            'name' => 'Default', 'slug' => 'default', 'is_active' => 1,
        ]);
        $this->minimalTheme = (int) (new ThemeModel())->insert([
            'name' => 'Minimal', 'slug' => 'minimal', 'is_active' => 0,
        ]);
    }

    protected int $authorId = 0;
    protected int $defaultTheme = 0;
    protected int $minimalTheme = 0;

    private function insertArticle(string $title, string $slug): int
    {
        $db = \Config\Database::connect();
        $db->table('content')->insert([
            'title' => $title,
            'slug' => $slug,
            'body' => "<p>{$title} body</p>",
            'status' => 'published',
            'content_type' => 'article',
            'author_id' => $this->authorId,
            'published_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
        ]);

        return (int) $db->insertID();
    }

    private function activate(int $id): void
    {
        (new ThemeModel())->activate($id);
        QueryCache::instance()->forget('theme');
    }

    public function testMinimalThemeRendersHomepage(): void
    {
        $this->insertArticle('Minimal Post', 'minimal-post');

        $this->activate($this->minimalTheme);

        $result = $this->get('/');
        $result->assertOK();
        $result->assertSee('Minimal Post');
        $this->assertStringContainsString('Georgia', $result->getBody());
    }

    public function testMinimalThemeRendersSinglePage(): void
    {
        $this->insertArticle('Minimal Article', 'minimal-article');

        $this->activate($this->minimalTheme);

        $result = $this->get('/content/minimal-article');
        $result->assertOK();
        $result->assertSee('Minimal Article');
        $result->assertSee('Minimal Article body');
    }

    public function testDefaultThemeRestoredAfterDeactivate(): void
    {
        $this->insertArticle('Restore Me', 'restore-me');

        $this->activate($this->minimalTheme);
        $this->activate($this->defaultTheme);

        $result = $this->get('/');
        $result->assertOK();
        $result->assertSee('Restore Me');
        $this->assertStringNotContainsString('post-list', $result->getBody());
    }

    public function testMinimalThemeConfigResolves(): void
    {
        $config = (new ThemeConfig())->resolve(['slug' => 'minimal']);

        $this->assertSame('narrow', $config['container_width'] ?? null);
        $this->assertSame('1', $config['show_author'] ?? null);
    }

    public function testMinimalThemeConfigSchemaVisibleInAdmin(): void
    {
        $this->post('/admin/auth/attempt', [
            'email' => 'm@kayacms.local',
            'password' => 'test123',
        ]);
        $this->session = $_SESSION;

        $result = $this->get('/admin/themes/config/' . $this->minimalTheme);
        $result->assertOK();
        $result->assertSee('Container Width');
        $result->assertSee('container_width');
    }

    public function testMinimalThemeConfigPersistsAndRenders(): void
    {
        $this->post('/admin/auth/attempt', [
            'email' => 'm@kayacms.local',
            'password' => 'test123',
        ]);
        $this->session = $_SESSION;

        $this->insertArticle('Config Post', 'config-post');

        $result = $this->post('/admin/themes/config/' . $this->minimalTheme, [
            'config' => [
                'container_width' => 'full',
                'show_author' => '0',
                'footer_text' => 'Minimal footer note',
            ],
        ]);
        $result->assertRedirectTo('/admin/themes');

        $this->activate($this->minimalTheme);

        $body = $this->get('/')->getBody();
        $this->assertStringContainsString('1200px', $body);
        $this->assertStringContainsString('Minimal footer note', $body);
    }
}