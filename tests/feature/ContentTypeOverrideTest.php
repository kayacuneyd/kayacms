<?php

namespace Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use App\Libraries\QueryCache;
use Content\Models\ContentModel;
use Theme\Libraries\ThemeConfig;
use Theme\Models\ThemeModel;
use User\Models\RoleModel;
use User\Models\UserModel;

class ContentTypeOverrideTest extends CIUnitTestCase
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
            'username' => 'ctadmin',
            'email' => 'ct@kayacms.local',
            'password_hash' => password_hash('test123', PASSWORD_DEFAULT),
            'role_id' => $roleId,
            'status' => 'active',
        ]);
        $this->authorId = (int) $userModel->findByEmail('ct@kayacms.local')->id;

        (new ThemeModel())->insert([
            'name' => 'Default',
            'slug' => 'default',
            'is_active' => 1,
        ]);
    }

    protected int $authorId = 0;

    private function insertContent(string $title, string $slug, string $type): int
    {
        $db = \Config\Database::connect();
        $db->table('content')->insert([
            'title' => $title,
            'slug' => $slug,
            'body' => "<p>{$title} body</p>",
            'status' => 'published',
            'content_type' => $type,
            'author_id' => $this->authorId,
            'published_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
        ]);

        return (int) $db->insertID();
    }

    public function testContentTypeUsesSpecificSingleView(): void
    {
        $this->insertContent('Phone X Review', 'phone-x', 'review');

        $result = $this->get('/content/phone-x');
        $result->assertOK();
        $result->assertSee('Phone X Review');
        $result->assertSee('Review');
        $result->assertSee('Phone X Review body');
    }

    public function testArticleFallsBackToGenericSingle(): void
    {
        $this->insertContent('Normal Post', 'normal-post', 'article');

        $result = $this->get('/content/normal-post');
        $result->assertOK();
        $result->assertSee('Normal Post');
        $result->assertSee('Normal Post body');
        $result->assertDontSee('Review');
    }

    public function testPageUsesGenericSingle(): void
    {
        $this->insertContent('About Page', 'about-page', 'page');

        $result = $this->get('/page/about-page');
        $result->assertOK();
        $result->assertSee('About Page');
    }

    public function testCustomDataTypeWithoutTemplateFallsBack(): void
    {
        $this->insertContent('Podcast Episode', 'podcast-one', 'podcast');

        $result = $this->get('/content/podcast-one');
        $result->assertOK();
        $result->assertSee('Podcast Episode');
        $result->assertSee('Podcast Episode body');
    }

    public function testReviewTemplateReadsCustomDataRating(): void
    {
        \Config\Database::connect()->table('content')->insert([
            'title' => 'The Gadget',
            'slug' => 'the-gadget',
            'body' => '<p>Main body.</p>',
            'status' => 'published',
            'content_type' => 'review',
            'author_id' => $this->authorId,
            'published_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
            'custom_data' => json_encode(['rating' => 4.5]),
        ]);

        $result = $this->get('/content/the-gadget');
        $result->assertOK();
        $result->assertSee('Rating: 4.5/5');
        $result->assertSee('Review');
    }
}