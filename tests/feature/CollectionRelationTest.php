<?php

namespace Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Content\Models\ContentCollectionItemModel;
use Content\Models\ContentCollectionModel;
use Content\Models\ContentModel;
use Content\Models\ContentRelationModel;
use Theme\Models\ThemeModel;
use User\Models\RoleModel;
use User\Models\UserModel;

class CollectionRelationTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resetServices();

        $db = \Config\Database::connect();
        $db->table('roles')->truncate();
        $db->table('users')->where('1=1')->delete();
        $db->table('content')->where('1=1')->delete();
        $db->table('comments')->where('1=1')->delete();
        $db->table('notifications')->where('1=1')->delete();
        $db->table('content_collections')->where('1=1')->delete();
        $db->table('content_collection_items')->where('1=1')->delete();
        $db->table('content_relations')->where('1=1')->delete();

        $roleModel = new RoleModel();
        $roleModel->insert(['name' => 'admin', 'permissions' => '["*"]']);
        $roleId = (int) $roleModel->getInsertID();

        $userModel = new UserModel();
        $userModel->insert([
            'username' => 'colluser',
            'email' => 'coll@kayacms.local',
            'password_hash' => password_hash('test123', PASSWORD_DEFAULT),
            'role_id' => $roleId,
            'status' => 'active',
        ]);

        $user = $userModel->findByEmail('coll@kayacms.local');

        $contentModel = new ContentModel();
        foreach (['Related One', 'Related Two', 'Standalone'] as $i => $title) {
            $contentModel->insert([
                'title' => $title,
                'slug' => strtolower(str_replace(' ', '-', $title)),
                'body' => "Body {$i}",
                'status' => 'published',
                'content_type' => 'article',
                'author_id' => (int) $user->id,
            ]);
        }
    }

    public function testCollectionsRequiresLogin(): void
    {
        $result = $this->get('/admin/collections');
        $result->assertRedirectTo('/admin/login');
    }

    public function testCollectionCanBeCreatedAndManaged(): void
    {
        $this->post('/admin/auth/attempt', [
            'email' => 'coll@kayacms.local',
            'password' => 'test123',
        ]);
        $this->session = $_SESSION;

        $result = $this->post('/admin/collections/store', [
            'name' => 'Best of',
            'slug' => 'best-of',
            'description' => 'Editor picks',
        ]);
        $result->assertRedirectTo('/admin/collections');

        $collection = (new ContentCollectionModel())->where('slug', 'best-of')->first();
        $this->assertNotNull($collection);

        $alpha = (new ContentModel())->where('slug', 'related-one')->first();

        $result = $this->post("/admin/collections/attach/{$collection['id']}", [
            'content_id' => $alpha->id,
        ]);
        $result->assertRedirectTo("/admin/collections/edit/{$collection['id']}");

        $items = (new ContentCollectionItemModel())->itemsFor((int) $collection['id']);
        $this->assertCount(1, $items);
        $this->assertSame((int) $alpha->id, (int) $items[0]['content_id']);

        $result = $this->get("/admin/collections/edit/{$collection['id']}");
        $result->assertOK();
        $result->assertSee('Best of');
        $result->assertSee('Related One');
    }

    public function testRelatedContentSavedFromForm(): void
    {
        $this->post('/admin/auth/attempt', [
            'email' => 'coll@kayacms.local',
            'password' => 'test123',
        ]);
        $this->session = $_SESSION;

        $alpha = (new ContentModel())->where('slug', 'related-one')->first();
        $beta  = (new ContentModel())->where('slug', 'related-two')->first();

        $result = $this->post("/admin/content/update/{$alpha->id}", [
            'title' => 'Alpha',
            'slug' => 'alpha',
            'body' => 'Body',
            'status' => 'published',
            'content_type' => 'article',
            'related_ids' => [(string) $beta->id],
            'is_featured' => '1',
        ]);
        $result->assertRedirectTo('/admin/content');

        $relationModel = new ContentRelationModel();
        $this->assertSame([(int) $beta->id], $relationModel->relatedIds((int) $alpha->id));

        $featured = (new ContentModel())->find($alpha->id);
        $this->assertSame(1, (int) $featured->is_featured);
    }

    public function testLinkRoutesExist(): void
    {
        $result = $this->get('/admin/collections/edit/1');
        $result->assertRedirectTo('/admin/login');
    }

    public function testFrontendShowsFeaturedAndRelated(): void
    {
        $db = \Config\Database::connect();
        $db->table('themes')->where('1=1')->delete();
        $themeModel = new ThemeModel();
        $defaultId = (int) $themeModel->insert([
            'name' => 'Default',
            'slug' => 'default',
            'is_active' => 0,
        ]);
        $themeModel->activate($defaultId);
        \App\Libraries\QueryCache::instance()->forget('theme');

        $db->table('settings')->where('1=1')->delete();
        $db->table('settings')->insertBatch([
            ['key' => 'site_default_locale', 'value' => 'tr', 'group' => 'general'],
            ['key' => 'site_active_locales', 'value' => 'tr', 'group' => 'general'],
            ['key' => 'site_name', 'value' => 'KayaCMS Test', 'group' => 'general'],
        ]);

        $contentModel = new ContentModel();
        $alpha = $contentModel->where('slug', 'related-one')->first();
        $beta  = $contentModel->where('slug', 'related-two')->first();

        $contentModel->update($alpha->id, [
            'is_featured' => 1,
            'published_at' => date('Y-m-d H:i:s'),
        ]);
        $contentModel->update($beta->id, ['status' => 'published', 'published_at' => date('Y-m-d H:i:s')]);

        (new ContentRelationModel())->addRelation((int) $alpha->id, (int) $beta->id);

        $result = $this->get('/');
        $result->assertOK();
        $result->assertSee('Featured Content');

        $result = $this->get('/content/related-one');
        $result->assertOK();
        $result->assertSee('Related Content');
        $result->assertSee('Related Two');
    }
}