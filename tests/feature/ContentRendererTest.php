<?php

namespace Feature;

use App\Libraries\ContentRenderer;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Content\Models\ContentModel;
use User\Models\RoleModel;
use User\Models\UserModel;

class ContentRendererTest extends CIUnitTestCase
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
            'username' => 'renderuser',
            'email' => 'render@kayacms.local',
            'password_hash' => password_hash('test123', PASSWORD_DEFAULT),
            'role_id' => $roleId,
            'status' => 'active',
        ]);

        (new ContentModel())->insert([
            'title' => 'Render Me',
            'slug' => 'render-me',
            'body' => '<p>Hello <b>world</b> <script>alert(1)</script> <a href="https://ok.example">link</a> <a href="javascript:bad()">x</a>.</p>',
            'excerpt' => '',
            'status' => 'published',
            'content_type' => 'article',
            'author_id' => (int) $userModel->findByEmail('render@kayacms.local')->id,
            'published_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function testSanitizeStripsScriptsAndUnsafeUrls(): void
    {
        $html = ContentRenderer::sanitize('<p>ok <script>alert(1)</script> <a href="javascript:x()">bad</a></p>');

        $this->assertStringNotContainsString('script', $html);
        $this->assertStringNotContainsString('javascript:', $html);
        $this->assertStringContainsString('ok', $html);
        $this->assertStringContainsString('bad', $html);
    }

    public function testSanitizeKeepsSafeLinksAndDropsEventHandlers(): void
    {
        $html = ContentRenderer::sanitize('<p>H <a href="https://example.com" onclick="x()">go</a> <img src="data:x" onerror="alert(1)" alt="i"></p>');

        $this->assertStringContainsString('https://example.com', $html);
        $this->assertStringNotContainsString('onclick', $html);
        $this->assertStringNotContainsString('onerror', $html);
    }

    public function testTextStripsAllMarkup(): void
    {
        $text = ContentRenderer::text('<p>Hello <b>world</b> &amp; goodbye</p>');

        $this->assertSame('Hello world & goodbye', $text);
    }

    public function testExcerptPrefersProvidedAndTruncatesOnWordBoundary(): void
    {
        $this->assertSame('My custom excerpt', ContentRenderer::excerpt('<p>x</p>', 'My custom excerpt'));

        $long = ContentRenderer::excerpt('<p>one two three four five six seven eight</p>', null, 12);
        $this->assertSame('one two…', $long);
    }

    public function testFrontendSinglePageRendersSanitizedBody(): void
    {
        $result = $this->get('/content/render-me');
        $result->assertOK();

        $body = (string) $result->getBody();
        $this->assertStringContainsString('Render Me', $body);
        $this->assertStringContainsString('world', $body);
        $this->assertStringNotContainsString('alert(1)', $body);

        // The sanitized article body must not carry script tags; extract the
        // `.content` area (the page footer legitimately injects the cookie
        // banner script, so restrict the assertion to the article markup).
        preg_match('/<div class="content">(.*?)<\/div>/s', $body, $match);
        $article = isset($match[1]) ? $match[0] : $body;
        $this->assertStringNotContainsString('<script', $article);
    }

    public function testFrontendExcerptNoLongerExposesRawTags(): void
    {
        $result = $this->get('/');
        $result->assertOK();

        $body = (string) $result->getBody();
        $this->assertStringContainsString('Render Me', $body);
        $this->assertStringContainsString('Hello world link', $body);
        $this->assertStringNotContainsString('<p>Hello <b>world</b>', $body);
        $this->assertStringNotContainsString('<script>alert(1)</script>', $body);
    }

    public function testAdminPreviewEndpointSanitizesBody(): void
    {
        $this->post('/admin/auth/attempt', [
            'email' => 'render@kayacms.local',
            'password' => 'test123',
        ]);
        $this->session = $_SESSION;

        $result = $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])
            ->post('/admin/content/preview', [
                'body' => '<p>Safe <script>alert(1)</script> text</p>',
            ]);

        $result->assertOK();
        $raw = (string) $result->getBody();
        $this->assertStringContainsString('Safe', $raw);
        $this->assertStringNotContainsString('<script>', $raw);
        $this->assertStringNotContainsString('alert(1)', $raw);
    }
}