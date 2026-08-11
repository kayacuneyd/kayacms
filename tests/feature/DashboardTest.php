<?php

namespace Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Content\Models\ContentModel;
use Content\Models\CommentModel;
use User\Models\RoleModel;
use User\Models\UserModel;

class DashboardTest extends CIUnitTestCase
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

        $roleModel = new RoleModel();
        $roleModel->insert(['name' => 'admin', 'permissions' => '["*"]']);
        $roleId = (int) $roleModel->getInsertID();

        $userModel = new UserModel();
        $userModel->insert([
            'username' => 'dashuser',
            'email' => 'dash@kayacms.local',
            'password_hash' => password_hash('test123', PASSWORD_DEFAULT),
            'role_id' => $roleId,
            'status' => 'active',
        ]);

        $user = $userModel->findByEmail('dash@kayacms.local');

        (new ContentModel())->insert([
            'title' => 'Dashboard Article',
            'slug' => 'dashboard-article',
            'body' => 'Body text',
            'status' => 'published',
            'content_type' => 'article',
            'author_id' => (int) $user->id,
        ]);
    }

    public function testDashboardRequiresLogin(): void
    {
        $result = $this->get('/admin/dashboard');
        $result->assertRedirectTo('/admin/login');
    }

    public function testDashboardLoadsWidgetsForLoggedInUser(): void
    {
        $this->post('/admin/auth/attempt', [
            'email' => 'dash@kayacms.local',
            'password' => 'test123',
        ]);
        $this->session = $_SESSION;

        $result = $this->get('/admin/dashboard');
        $result->assertOK();
        $result->assertSee('Total Content');
        $result->assertSee('Dashboard Article');
        $result->assertSee('Pending Comments');
        $result->assertSee('KayaCMS v' . \Config\Version::current());
    }

    public function testCommentsPageSupportsStatusFilter(): void
    {
        $this->post('/admin/auth/attempt', [
            'email' => 'dash@kayacms.local',
            'password' => 'test123',
        ]);
        $this->session = $_SESSION;

        $content = (new ContentModel())->where('slug', 'dashboard-article')->first();

        (new CommentModel())->insert([
            'content_id' => (int) $content->id,
            'author_name' => 'Tester',
            'author_email' => 'tester@example.com',
            'body' => 'A pending comment body',
            'status' => 'pending',
        ]);

        $result = $this->get('/admin/comments?status=pending');
        $result->assertOK();
        $result->assertSee('A pending comment body');

        $result = $this->get('/admin/comments?status=approved');
        $result->assertOK();
        $result->assertDontSee('A pending comment body');
    }
}