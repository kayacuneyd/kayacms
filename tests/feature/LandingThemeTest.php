<?php

namespace Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use App\Libraries\QueryCache;
use Theme\Libraries\ThemeConfig;
use Theme\Models\ThemeModel;
use User\Models\RoleModel;
use User\Models\UserModel;

class LandingThemeTest extends CIUnitTestCase
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
            'username' => 'ladmin',
            'email' => 'l@kayacms.local',
            'password_hash' => password_hash('test123', PASSWORD_DEFAULT),
            'role_id' => $roleId,
            'status' => 'active',
        ]);
        $this->authorId = (int) $userModel->findByEmail('l@kayacms.local')->id;

        $this->defaultTheme = (int) (new ThemeModel())->insert([
            'name' => 'Default', 'slug' => 'default', 'is_active' => 1,
        ]);
        $this->landingTheme = (int) (new ThemeModel())->insert([
            'name' => 'Landing', 'slug' => 'landing', 'is_active' => 0,
        ]);
    }

    protected int $authorId = 0;
    protected int $defaultTheme = 0;
    protected int $landingTheme = 0;

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

    public function testLandingThemeRendersHomepage(): void
    {
        $this->insertArticle('Landing Post', 'landing-post');

        $this->activate($this->landingTheme);

        $result = $this->get('/');
        $result->assertOK();
        $result->assertSee('Landing Post');
        $this->assertStringContainsString('0b1020', $result->getBody());
    }

    public function testLandingThemeRendersConfigDrivenHero(): void
    {
        $this->post('/admin/auth/attempt', [
            'email' => 'l@kayacms.local',
            'password' => 'test123',
        ]);
        $this->session = $_SESSION;

        $this->post('/admin/themes/config/' . $this->landingTheme, [
            'config' => [
                'hero_headline'    => 'Ship your landing fast',
                'hero_subheadline' => 'A headline driven by theme config.',
                'hero_cta_text'    => 'Take Action',
                'hero_cta_url'     => '/contact',
                'footer_email'     => 'hi@kayacms.local',
            ],
        ]);

        $this->activate($this->landingTheme);

        $body = $this->get('/')->getBody();
        $this->assertStringContainsString('Ship your landing fast', $body);
        $this->assertStringContainsString('A headline driven by theme config.', $body);
        $this->assertStringContainsString('Take Action', $body);
        $this->assertStringContainsString('Take Action', $body);
    }

    public function testLandingThemeParsesFeatureLines(): void
    {
        $features = "🚀|Speed|Instant pages.\n🔒|Security|Locked down.\n🧩|Modules|Everything included.";

        $this->post('/admin/auth/attempt', [
            'email' => 'l@kayacms.local',
            'password' => 'test123',
        ]);
        $this->session = $_SESSION;

        $this->post('/admin/themes/config/' . $this->landingTheme, [
            'config' => ['features' => $features],
        ]);

        $this->activate($this->landingTheme);

        $body = $this->get('/')->getBody();
        $this->assertStringContainsString('Speed', $body);
        $this->assertStringContainsString('Instant pages.', $body);
        $this->assertStringContainsString('Security', $body);
        $this->assertStringContainsString('Modules', $body);
    }

    public function testLandingThemeRendersSinglePage(): void
    {
        $this->insertArticle('Landing Article', 'landing-article');

        $this->activate($this->landingTheme);

        $result = $this->get('/content/landing-article');
        $result->assertOK();
        $result->assertSee('Landing Article');
        $result->assertSee('Landing Article body');
    }

    public function testLandingThemeConfigSchemaVisibleInAdmin(): void
    {
        $this->post('/admin/auth/attempt', [
            'email' => 'l@kayacms.local',
            'password' => 'test123',
        ]);
        $this->session = $_SESSION;

        $result = $this->get('/admin/themes/config/' . $this->landingTheme);
        $result->assertOK();
        $result->assertSee('Hero Headline');
        $result->assertSee('hero_headline');
        $result->assertSee('Features (Icon|Title|Description per line)');
    }

    public function testLandingThemeDisabledArticlesSection(): void
    {
        $this->insertArticle('Hidden Post', 'hidden-post');

        $this->post('/admin/auth/attempt', [
            'email' => 'l@kayacms.local',
            'password' => 'test123',
        ]);
        $this->session = $_SESSION;

        $this->post('/admin/themes/config/' . $this->landingTheme, [
            'config' => ['show_articles' => '0'],
        ]);

        $this->activate($this->landingTheme);

        $result = $this->get('/');
        $result->assertOK();
        $result->assertDontSee('Hidden Post');
    }

    public function testLandingThemeRendersContactPage(): void
    {
        $db = \Config\Database::connect();
        $db->table('contact_forms')->where('1=1')->delete();
        $db->table('contact_forms')->insert([
            'name' => 'İletişim',
            'slug' => 'contact',
            'fields' => json_encode([
                ['name' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => true],
                ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true],
                ['name' => 'message', 'label' => 'Message', 'type' => 'textarea', 'required' => true],
            ]),
            'settings' => '{}',
            'is_active' => 1,
        ]);

        $this->activate($this->landingTheme);

        $result = $this->get('/contact');
        $result->assertOK();
        $result->assertSee('İletişim');
        $result->assertSee('contact/submit');
    }
}