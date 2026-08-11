<?php

namespace Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use User\Models\RoleModel;
use User\Models\UserModel;
use User\Libraries\ApiToken;
use User\Libraries\Webhook;

class ApiTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resetServices();

        $db = \Config\Database::connect();
        $db->table('roles')->truncate();
        $db->table('users')->where('1=1')->delete();
        $db->table('api_tokens')->where('1=1')->delete();
        $db->table('api_rate_limits')->where('1=1')->delete();
        $db->table('webhooks')->where('1=1')->delete();
        $db->table('webhook_deliveries')->where('1=1')->delete();

        $roleModel = new RoleModel();
        $roleModel->insert(['name' => 'admin', 'permissions' => '["*"]']);
        $roleId = (int) $roleModel->getInsertID();

        (new UserModel())->insert([
            'username' => 'apiuser',
            'email' => 'api@kayacms.local',
            'password_hash' => password_hash('test123', PASSWORD_DEFAULT),
            'role_id' => $roleId,
            'status' => 'active',
        ]);
    }

    public function testApiLoginReturnsToken(): void
    {
        $result = $this->withBodyFormat('json')->post('/api/auth/login', [
            'email' => 'api@kayacms.local',
            'password' => 'test123',
        ]);

        $result->assertStatus(200);
        $json = $this->extractJson((string) $result->getBody());
        $this->assertTrue($json['success'] ?? false);
        $this->assertNotEmpty($json['token'] ?? '');
    }

    public function testApiRejectsBadCredentials(): void
    {
        $result = $this->withBodyFormat('json')->post('/api/auth/login', [
            'email' => 'api@kayacms.local',
            'password' => 'wrongpass',
        ]);

        $result->assertStatus(401);
    }

    public function testApiTokenPersonalAccessTokenWorks(): void
    {
        $user = (new UserModel())->findByEmail('api@kayacms.local');
        $issued = (new ApiToken())->create((int) $user->id, 'CLI token', ['content.read']);

        $result = $this->withHeaders(['API-Key' => $issued['plain']])->get('/api/auth/me');
        $result->assertStatus(200);
        $result->assertSee('apiuser');
    }

    public function testOpenApiSpecIsServed(): void
    {
        $result = $this->get('/api/openapi');
        $result->assertStatus(200);
        $result->assertHeader('Content-Type', 'application/json; charset=UTF-8');

        $raw = (string) $result->getBody();
        $json = $this->extractJson($raw);

        $this->assertSame('3.0.3', $json['openapi'] ?? null);
        $this->assertNotEmpty($json['paths'] ?? []);
    }

    private function extractJson(string $raw): ?array
    {
        $start = strpos($raw, '{');
        $end   = strrpos($raw, '}');

        if ($start === false || $end === false || $end < $start) {
            return null;
        }

        return json_decode(substr($raw, $start, $end - $start + 1), true);
    }

    public function testWebhookDeliveriesRecordedGracefully(): void
    {
        $db = \Config\Database::connect();
        $db->table('webhooks')->insert([
            'name'      => 'Test Hook',
            'url'       => 'http://localhost:1/nonexistent',
            'event'     => 'content.deleted',
            'secret'    => 'secret123',
            'is_active' => 1,
        ]);

        (new Webhook())->dispatch('content.deleted', ['id' => 5, 'title' => 'x']);

        $count = $db->table('webhook_deliveries')->countAllResults();
        $this->assertSame(1, $count);
    }

    public function testRateLimitBlocked(): void
    {
        // Limit of 5 in 1h window on /api/auth/me; exceed it.
        $user = (new UserModel())->findByEmail('api@kayacms.local');
        $issued = (new ApiToken())->create((int) $user->id, 'rl');

        $last = null;
        for ($i = 0; $i < 8; $i++) {
            $last = $this->withHeaders(['API-Key' => $issued['plain']])->get('/api/auth/me');
        }

        // Expect final status 429.
        $this->assertSame(429, $last->response()->getStatusCode());
    }
}