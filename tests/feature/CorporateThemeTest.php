<?php

namespace Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use App\Libraries\QueryCache;
use Theme\Libraries\ThemeConfig;
use Theme\Models\ThemeModel;
use User\Models\RoleModel;
use User\Models\UserModel;

class CorporateThemeTest extends CIUnitTestCase
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
            'username' => 'corpadmin',
            'email' => 'corp@kayacms.local',
            'password_hash' => password_hash('test123', PASSWORD_DEFAULT),
            'role_id' => $roleId,
            'status' => 'active',
        ]);
        $this->authorId = (int) $userModel->findByEmail('corp@kayacms.local')->id;

        $this->defaultTheme = (int) (new ThemeModel())->insert([
            'name' => 'Default', 'slug' => 'default', 'is_active' => 1,
        ]);
        $this->corporateTheme = (int) (new ThemeModel())->insert([
            'name' => 'Corporate', 'slug' => 'corporate', 'is_active' => 0,
        ]);
    }

    protected int $authorId = 0;
    protected int $defaultTheme = 0;
    protected int $corporateTheme = 0;

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

    private function login(): void
    {
        $this->post('/admin/auth/attempt', [
            'email' => 'corp@kayacms.local',
            'password' => 'test123',
        ]);
        $this->session = $_SESSION;
    }

    public function testCorporateThemeRendersHomepage(): void
    {
        $this->insertArticle('Corporate Post', 'corporate-post');

        $this->activate($this->corporateTheme);

        $result = $this->get('/');
        $result->assertOK();
        $result->assertSee('Corporate Post');
        $this->assertStringContainsString('Playfair Display', $result->getBody());
    }

    public function testCorporateThemeRendersHeroSlidesFromRepeater(): void
    {
        $this->login();

        $this->post('/admin/themes/config/' . $this->corporateTheme, [
            'config' => [
                'hero' => [
                    ['headline' => 'Uzman Kadro', 'image' => '/uploads/hero-1.png', 'name' => 'Uzman Kadro', 'desc' => 'Uzman avukatlarımızla yanınızdayız.'],
                    ['headline' => 'Güvenilir Danışmanlık', 'image' => '/uploads/hero-2.png', 'name' => 'Güvenilir Danışmanlık', 'desc' => 'Şeffaf ve etik hizmet.'],
                ],
            ],
        ]);

        $this->activate($this->corporateTheme);

        $body = $this->get('/')->getBody();
        $this->assertStringContainsString('Uzman Kadro', $body);
        $this->assertStringContainsString('Güvenilir Danışmanlık', $body);
        $this->assertStringContainsString('hero-swiper', $body);
        $this->assertStringContainsString('hero-pagination', $body);
    }

    public function testCorporateThemeRendersPracticeAndReferencesSliders(): void
    {
        $this->login();

        $this->post('/admin/themes/config/' . $this->corporateTheme, [
            'config' => [
                'practice_title' => 'Çalışma Alanlarımız',
                'practice' => [
                    ['icon' => '/uploads/trade.png', 'title' => 'Ceza Hukuku', 'desc' => 'Ceza davaları uzmanlık alanımızdır.'],
                    ['icon' => '/uploads/family.png', 'title' => 'Tıp/Sağlık Hukuku', 'desc' => 'Sağlık hukuku davaları.'],
                ],
                'references_title' => 'Referanslarımız',
                'references' => [
                    ['name' => 'Referans-1', 'quote' => 'Memnun kaldık.'],
                    ['name' => 'Referans-2', 'quote' => 'Profesyonel hizmet.'],
                ],
            ],
        ]);

        $this->activate($this->corporateTheme);

        $body = $this->get('/')->getBody();
        $this->assertStringContainsString('Ceza Hukuku', $body);
        $this->assertStringContainsString('Tıp/Sağlık Hukuku', $body);
        $this->assertStringContainsString('Referanslarımız', $body);
        $this->assertStringContainsString('Referans-1', $body);
        $this->assertStringContainsString('practice-swiper', $body);
        $this->assertStringContainsString('refs-swiper', $body);
    }

    public function testCorporateThemeRendersSinglePage(): void
    {
        $this->insertArticle('Corporate Article', 'corporate-article');

        $this->activate($this->corporateTheme);

        $result = $this->get('/content/corporate-article');
        $result->assertOK();
        $result->assertSee('Corporate Article');
        $result->assertSee('Corporate Article body');
    }

    public function testCorporateThemeConfigSchemaInAdmin(): void
    {
        $this->login();

        $result = $this->get('/admin/themes/config/' . $this->corporateTheme);
        $result->assertOK();
        $result->assertSee('Hero Slides (repeater)');
        $result->assertSee('Practice Areas (repeater)');
        $result->assertSee('Team Members (repeater)');
        $result->assertSee('References (repeater)');
    }

    public function testCorporateThemeTeamTemplateUsesRepeater(): void
    {
        $this->login();

        $this->post('/admin/themes/config/' . $this->corporateTheme, [
            'config' => [
                'team_title' => 'Takımımız',
                'team' => [
                    ['photo' => '/uploads/av.jpg', 'name' => 'Av. Doç. Dr. Mahmut Kaplan', 'email' => 'mkaplan@kzhukuk.com'],
                ],
            ],
        ]);

        $this->activate($this->corporateTheme);

        $db = \Config\Database::connect();
        $db->table('virtual_pages')->where('1=1')->delete();
        $db->table('virtual_pages')->insert([
            'slug' => 'takim',
            'title' => 'Takımımız',
            'handler' => 'template',
            'payload' => json_encode(['view' => 'themes/corporate/takim']),
        ]);
        QueryCache::instance()->forget('theme');

        $result = $this->get('/takim');
        $result->assertOK();
        $this->assertStringContainsString('Av. Doç. Dr. Mahmut Kaplan', $result->getBody());
    }

    public function testCorporateThemeRendersContactPage(): void
    {
        $db = \Config\Database::connect();
        $db->table('contact_forms')->where('1=1')->delete();
        $db->table('contact_forms')->insert([
            'name' => 'İletişim',
            'slug' => 'contact',
            'fields' => json_encode([
                ['name' => 'name', 'label' => 'Ad', 'type' => 'text', 'required' => true],
                ['name' => 'message', 'label' => 'Mesaj', 'type' => 'textarea', 'required' => true],
            ]),
            'settings' => '{}',
            'is_active' => 1,
        ]);

        $this->activate($this->corporateTheme);

        $result = $this->get('/contact');
        $result->assertOK();
        $result->assertSee('İletişim');
        $result->assertSee('contact/submit');
        $result->assertSee('form-card');
    }
}
