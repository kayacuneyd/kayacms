<?php

namespace Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use App\Libraries\QueryCache;
use Maintenance\Libraries\BackupManager;
use Media\Models\MediaJobModel;
use Setting\Models\SettingModel;
use Theme\Models\ThemeModel;
use User\Models\RoleModel;
use User\Models\UserModel;

/**
 * Note: in this test environment CodeIgniter wraps JSON responses in a plain
 * HTML document (see ApiTest, which handles the same). We reuse the same
 * extractJson() approach here.
 */
class CronTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    private function extractJson(string $raw): ?array
    {
        $start = strpos($raw, '{');
        $end   = strrpos($raw, '}');

        if ($start === false || $end === false || $end < $start) {
            return null;
        }

        return json_decode(substr($raw, $start, $end - $start + 1), true);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->resetServices();

        $db = \Config\Database::connect();
        $db->table('roles')->truncate();
        $db->table('users')->where('1=1')->delete();
        $db->table('themes')->where('1=1')->delete();
        $db->table('media_jobs')->where('1=1')->delete();
        $db->table('backups')->where('1=1')->delete();
        QueryCache::instance()->forget('theme');

        $roleModel = new RoleModel();
        $roleModel->insert(['name' => 'admin', 'permissions' => '["*"]']);
        $roleId = (int) $roleModel->getInsertID();

        (new UserModel())->insert([
            'username' => 'cronadmin',
            'email' => 'cron@kayacms.local',
            'password_hash' => password_hash('test123', PASSWORD_DEFAULT),
            'role_id' => $roleId,
            'status' => 'active',
        ]);

        (new ThemeModel())->insert([
            'name' => 'Default',
            'slug' => 'default',
            'is_active' => 1,
        ]);
    }

    private function login(): void
    {
        $this->post('/admin/auth/attempt', [
            'email' => 'cron@kayacms.local',
            'password' => 'test123',
        ]);
        $this->session = $_SESSION;
    }

    public function testWebCronRejectsMissingToken(): void
    {
        (new SettingModel())->setSetting('cron_token', '', 'system', 'string');

        $result = $this->get('/cron/run/whatever');
        $result->assertStatus(403);
    }

    public function testWebCronRejectsWrongToken(): void
    {
        (new SettingModel())->setSetting('cron_token', 'secret-token', 'system', 'string');

        $result = $this->get('/cron/run/wrong-token');
        $result->assertStatus(403);
    }

    public function testWebCronRunsBackupTask(): void
    {
        (new SettingModel())->setSetting('cron_token', 'secret-token', 'system', 'string');
        (new SettingModel())->setSetting('cron_tasks', 'backup:create', 'system', 'string');

$result = $this->get('/cron/run/secret-token');
        $result->assertStatus(200);

        $body = $this->extractJson((string) $result->getBody());
        $this->assertIsArray($body);
        $this->assertTrue($body['ok']);
        $this->assertArrayHasKey('backup:create', $body['results']);

        $backup = (new BackupManager())->all();
        $this->assertNotEmpty($backup);
        $this->assertSame('success', $backup[0]['status']);
    }

    public function testWebCronRunsMediaQueueTask(): void
    {
        (new SettingModel())->setSetting('cron_token', 'secret-token', 'system', 'string');
        (new SettingModel())->setSetting('cron_tasks', 'media:queue', 'system', 'string');

        $db = \Config\Database::connect();
        $db->table('media_jobs')->insert([
            'type'         => 'thumbnail',
            'media_id'     => null,
            'payload'      => '{}',
            'status'       => 'pending',
            'attempts'     => 0,
            'max_attempts' => 1,
            'available_at' => date('Y-m-d H:i:s'),
        ]);

        $result = $this->get('/cron/run/secret-token');
        $result->assertStatus(200);

        $job = (new MediaJobModel())->orderBy('id', 'DESC')->first();
        $this->assertSame('failed', $job['status']);
        $this->assertStringContainsString('not found', $job['error']);
    }

    public function testWebCronRunsBothTasks(): void
    {
        (new SettingModel())->setSetting('cron_token', 'secret-token', 'system', 'string');
        (new SettingModel())->setSetting('cron_tasks', 'media:queue,backup:create', 'system', 'string');

        $result = $this->get('/cron/run/secret-token');
        $result->assertStatus(200);

        $body = $this->extractJson((string) $result->getBody());
        $this->assertIsArray($body);
        $this->assertArrayHasKey('media:queue', $body['results']);
        $this->assertArrayHasKey('backup:create', $body['results']);
    }

    public function testAdminCanGenerateAndUpdateCronToken(): void
    {
        $this->login();

        $result = $this->post('/admin/maintenance/cron/token');
        $result->assertRedirectTo('/admin/maintenance');

        $token = (new SettingModel())->getSetting('cron_token', '');
        $this->assertNotEmpty($token);
        $this->assertMatchesRegularExpression('/^[a-f0-9]{48}$/', $token);

        $result = $this->post('/admin/maintenance/cron', [
            'cron_token' => 'my-new-token',
            'cron_tasks' => 'backup:create',
        ]);
        $result->assertRedirectTo('/admin/maintenance');

        $this->assertSame('my-new-token', (new SettingModel())->getSetting('cron_token', ''));
        $this->assertSame('backup:create', (new SettingModel())->getSetting('cron_tasks', ''));

        $result = $this->get('/cron/run/my-new-token');
        $result->assertStatus(200);
    }

    public function testAdminRejectsInvalidTokenCharacters(): void
    {
        $this->login();

        $result = $this->post('/admin/maintenance/cron', [
            'cron_token' => 'bad token!',
            'cron_tasks' => 'media:queue',
        ]);
        $result->assertRedirectTo('/admin/maintenance');
        $result->assertSessionHas('error');
    }

    public function testMaintenanceIndexShowsCronCard(): void
    {
        $this->login();

        $result = $this->get('/admin/maintenance');
        $result->assertOK();
        $result->assertSee('Web Cron');
        $result->assertSee('cron_token');
    }
}