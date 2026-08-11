<?php

namespace Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use User\Libraries\GdprExport;
use User\Models\RoleModel;
use User\Models\UserModel;

class GdprTest extends CIUnitTestCase
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
        $db->table('media')->where('1=1')->delete();
        $db->table('activity_logs')->where('1=1')->delete();
        $db->table('security_logs')->where('1=1')->delete();

        $roleModel = new RoleModel();
        $roleModel->insert(['name' => 'admin', 'permissions' => '["*"]']);
        $roleId = (int) $roleModel->getInsertID();

        (new UserModel())->insert([
            'username' => 'gdpruser',
            'email' => 'gdpr@kayacms.local',
            'password_hash' => password_hash('test123', PASSWORD_DEFAULT),
            'role_id' => $roleId,
            'status' => 'active',
        ]);
    }

    private function userId(): int
    {
        $user = (new UserModel())->findByEmail('gdpr@kayacms.local');
        return (int) $user->id;
    }

    private function login(): void
    {
        $this->post('/admin/auth/attempt', [
            'email' => 'gdpr@kayacms.local',
            'password' => 'test123',
        ]);
        $this->session = $_SESSION;
    }

    public function testGdprPageRequiresLogin(): void
    {
        $result = $this->get('/admin/gdpr');
        $result->assertRedirectTo('/admin/login');
    }

    public function testGdprPageLoadsForLoggedInUser(): void
    {
        $this->login();

        $result = $this->get('/admin/gdpr');
        $result->assertOK();
        $result->assertSee('GDPR Export');
    }

    public function testCollectPullsContentMediaAndRedactsSecrets(): void
    {
        $db   = \Config\Database::connect();
        $uid  = $this->userId();

        $db->table('content')->insert([
            'title' => 'Seeded article',
            'slug'  => 'seeded-' . $uid,
            'body'  => '<p>Hello</p>',
            'content_type' => 'article',
            'status' => 'published',
            'author_id' => $uid,
            'locale' => 'tr',
        ]);

        $db->table('media')->insert([
            'filename' => 'photo.jpg',
            'original_name' => 'photo.jpg',
            'mime_type' => 'image/jpeg',
            'size' => 1024,
            'path' => '/uploads/photo.jpg',
            'uploaded_by' => $uid,
        ]);

        $db->table('comments')->insert([
            'content_id' => 1,
            'author_name' => 'gdpr',
            'author_email' => 'gdpr@kayacms.local',
            'body' => 'A comment',
            'status' => 'approved',
        ]);

        $export = (new GdprExport())->collect($uid, 'gdpr@kayacms.local');

        $this->assertSame('gdpr@kayacms.local', $export['subject']['email']);
        $this->assertArrayNotHasKey('password_hash', $export['subject']);
        $this->assertCount(1, $export['content']);
        $this->assertCount(1, $export['media']);
        $this->assertCount(1, $export['comments']);
    }

    public function testJsonExportContainsNoPasswordHash(): void
    {
        $uid   = $this->userId();
        $json  = (new GdprExport())->toJson($uid, 'gdpr@kayacms.local');
        $data  = json_decode($json, true);

        $this->assertIsArray($data);
        $this->assertArrayNotHasKey('password_hash', $data['subject']);
    }

    public function testCsvExportFlattensProfile(): void
    {
        $uid = $this->userId();
        $csv = (new GdprExport())->toCsv($uid, 'gdpr@kayacms.local');

        $this->assertStringStartsWith("path,value\n", $csv);
        $this->assertStringContainsString('subject.email,gdpr@kayacms.local', $csv);
    }

    public function testExportEndpointStreamsJsonAttachment(): void
    {
        $this->login();

        $db  = \Config\Database::connect();
        $uid = $this->userId();

        $result = $this->get("/admin/gdpr/export/{$uid}?format=json");
        $result->assertOK();

        $headers = $result->response()->getHeaderLine('Content-Type');
        $this->assertStringContainsString('application/json', $headers);
        $this->assertStringContainsString('attachment', $result->response()->getHeaderLine('Content-Disposition'));

        $body = (string) $result->response()->getBody();
        $data = json_decode($body, true);
        $this->assertSame('gdpr@kayacms.local', $data['subject']['email']);
    }

    public function testExportEndpointStreamsCsv(): void
    {
        $this->login();

        $uid = $this->userId();

        $result = $this->get("/admin/gdpr/export/{$uid}?format=csv");
        $result->assertOK();

        $this->assertStringContainsString('text/csv', $result->response()->getHeaderLine('Content-Type'));
        $this->assertStringStartsWith("path,value\n", (string) $result->response()->getBody());
    }

    public function testDeleteDataRemovesUserAccount(): void
    {
        $roleModel = new RoleModel();
        (new UserModel())->insert([
            'username' => 'doomed',
            'email' => 'doomed@kayacms.local',
            'password_hash' => password_hash('test123', PASSWORD_DEFAULT),
            'role_id' => (int) $roleModel->getInsertID(),
            'status' => 'active',
        ]);

        $this->login();

        $doomed = (new UserModel())->findByEmail('doomed@kayacms.local');

        $result = $this->post("/admin/gdpr/delete-data/{$doomed->id}");
        $result->assertRedirect();
        $result->assertSessionHas('success');

        $this->assertNull((new UserModel())->findByEmail('doomed@kayacms.local'));
    }
}