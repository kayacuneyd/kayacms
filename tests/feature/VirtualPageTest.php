<?php

namespace Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Content\Libraries\VirtualPage as VirtualPageLibrary;
use Content\Models\VirtualPageModel;
use User\Models\RoleModel;
use User\Models\UserModel;

class VirtualPageTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resetServices();

        $db = \Config\Database::connect();
        $db->table('roles')->truncate();
        $db->table('users')->where('1=1')->delete();
        $db->table('virtual_pages')->where('1=1')->delete();

        $roleModel = new RoleModel();
        $roleModel->insert(['name' => 'admin', 'permissions' => '["*"]']);
        $roleId = (int) $roleModel->getInsertID();

        (new UserModel())->insert([
            'username' => 'vpuser',
            'email' => 'vp@kayacms.local',
            'password_hash' => password_hash('test123', PASSWORD_DEFAULT),
            'role_id' => $roleId,
            'status' => 'active',
        ]);
    }

    private function login(): void
    {
        $this->post('/admin/auth/attempt', [
            'email' => 'vp@kayacms.local',
            'password' => 'test123',
        ]);
        $this->session = $_SESSION;
    }

    public function testModelResolvesActiveBySlug(): void
    {
        $model = new VirtualPageModel();
        $model->insert([
            'slug' => 'features',
            'title' => 'Features',
            'handler' => 'markdown',
            'payload' => json_encode(['body' => '# Hello']),
            'status' => 'active',
        ]);

        $page = $model->findBySlug('features');
        $this->assertNotNull($page);
        $this->assertSame('Features', $page['title']);
        $this->assertSame(['body' => '# Hello'], $model->payloadArray($page));

        $model->newQuery()->where('slug', 'features')->set('status', 'inactive')->update();
        $this->assertNull($model->findBySlug('features'));
    }

    public function testRenderMarkdown(): void
    {
        $vp = new VirtualPageLibrary();

        $source = "# Title\n\nA paragraph with **bold** and `code`.\n\n- one\n- two";
        $html = $vp->renderMarkdown($source);

        $this->assertStringContainsString('<h1>Title</h1>', $html);
        $this->assertStringContainsString('<strong>bold</strong>', $html);
        $this->assertStringContainsString('<code>code</code>', $html);
        $this->assertStringContainsString('<li>one</li>', $html);
        $this->assertStringContainsString('<li>two</li>', $html);
    }

    public function testValidatePayload(): void
    {
        $vp = new VirtualPageLibrary();

        $this->assertSame([], $vp->validatePayload('template', ['view' => 'themes/default/virtual']));
        $this->assertStringContainsString('view name', $vp->validatePayload('template', [])[0]);
        $this->assertStringContainsString('target URL', $vp->validatePayload('redirect', [])[0]);
        $this->assertStringContainsString('body', $vp->validatePayload('markdown', [])[0]);
    }

    public function testFrontendRendersMarkdownVirtualPage(): void
    {
        $model = new VirtualPageModel();
        $model->insert([
            'slug' => 'about-vp',
            'title' => 'About Us',
            'handler' => 'markdown',
            'payload' => json_encode(['body' => "## Who we are\n\nWe build software."]),
            'status' => 'active',
        ]);

        $result = $this->get('/about-vp');
        $result->assertOK();
        $result->assertSee('About Us');
        $result->assertSee('Who we are');
    }

    public function testFrontend404ForUnknownSlug(): void
    {
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        (new VirtualPageLibrary())->dispatch('definitely-not-a-page');
    }

    public function testFrontendRedirectVirtualPage(): void
    {
        $model = new VirtualPageModel();
        $model->insert([
            'slug' => 'legacy-link',
            'title' => 'Legacy',
            'handler' => 'redirect',
            'payload' => json_encode(['url' => '/page/about']),
            'status' => 'active',
        ]);

        $result = $this->get('/legacy-link');
        $this->assertTrue($result->isRedirect());
    }

    public function testInactiveVirtualPageIs404(): void
    {
        $model = new VirtualPageModel();
        $model->insert([
            'slug' => 'hidden',
            'title' => 'Hidden',
            'handler' => 'markdown',
            'payload' => json_encode(['body' => 'secret']),
            'status' => 'inactive',
        ]);

        // Library dispatch ignores inactive pages → 404 via exception.
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        (new VirtualPageLibrary())->dispatch('hidden');
    }

    public function testAdminIndexLoads(): void
    {
        $this->login();

        $result = $this->get('/admin/virtual-pages');
        $result->assertOK();
        $result->assertSee('Virtual Pages');
    }

    public function testAdminRootNotSwallowedBySingleSegmentCatchAll(): void
    {
        // Regression: the virtual-page catch-all '/admin' must not capture
        // the single-segment, otherwise /admin returns a 404.
        $result = $this->get('/admin');
        $result->assertOK();
        $result->assertSee('Login');
    }

    public function testAdminCreatesVirtualPage(): void
    {
        $this->login();

        $result = $this->post('/admin/virtual-pages/store', [
            'slug' => 'testing-vp',
            'title' => 'Testing VP',
            'handler' => 'markdown',
            'payload_body' => '## Testing\n\nBody here.',
            'payload_view' => '',
            'payload_url' => '',
            'status' => 'active',
        ]);

        $result->assertRedirectTo('/admin/virtual-pages');
        $result->assertSessionHas('success');

        $page = (new VirtualPageModel())->findBySlug('testing-vp');
        $this->assertNotNull($page);
        $this->assertSame('markdown', $page['handler']);
    }

    public function testAdminRejectsMissingRedirectUrl(): void
    {
        $this->login();

        $result = $this->post('/admin/virtual-pages/store', [
            'slug' => 'bad-redirect',
            'title' => 'Bad',
            'handler' => 'redirect',
            'payload_body' => '',
            'payload_view' => '',
            'payload_url' => '',
            'status' => 'active',
        ]);

        $result->assertSessionHas('error');
        $this->assertNull((new VirtualPageModel())->findBySlug('bad-redirect'));
    }

    public function testAdminEditsVirtualPage(): void
    {
        $this->login();

        $model = new VirtualPageModel();
        $id = $model->insert([
            'slug' => 'editable-vp',
            'title' => 'Editable',
            'handler' => 'markdown',
            'payload' => json_encode(['body' => 'original']),
            'status' => 'active',
        ]);

        $result = $this->post("/admin/virtual-pages/update/{$id}", [
            'slug' => 'editable-vp-v2',
            'title' => 'Editable V2',
            'handler' => 'template',
            'payload_body' => '',
            'payload_view' => 'themes/default/virtual',
            'payload_url' => '',
            'status' => 'active',
        ]);

        $result->assertRedirectTo('/admin/virtual-pages');
        $result->assertSessionHas('success');

        $page = $model->find($id);
        $this->assertSame('editable-vp-v2', $page['slug']);
        $this->assertSame('template', $page['handler']);
    }
}