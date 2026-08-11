<?php

namespace Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use User\Models\RoleModel;
use User\Models\UserModel;

class BackupMaintenanceTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resetServices();

        $db = \Config\Database::connect();
        $db->table('roles')->truncate();
        $db->table('users')->where('1=1')->delete();
        $db->table('settings')->where('1=1')->delete();
        $db->table('backups')->where('1=1')->delete();
        $db->table('security_logs')->where('1=1')->delete();

        foreach (glob(WRITEPATH . 'backups/*.sqlite') ?: [] as $file) {
            @unlink($file);
        }

        $roleModel = new RoleModel();
        $roleModel->insert(['name' => 'admin', 'permissions' => '["*"]']);
        $roleId = (int) $roleModel->getInsertID();

        $userModel = new UserModel();
        $userModel->insert([
            'username' => 'bkadmin',
            'email' => 'bk@kayacms.local',
            'password_hash' => password_hash('test123', PASSWORD_DEFAULT),
            'role_id' => $roleId,
            'status' => 'active',
        ]);
    }

    private function login(): void
    {
        $this->post('/admin/auth/attempt', [
            'email' => 'bk@kayacms.local',
            'password' => 'test123',
        ]);
        $this->session = $_SESSION;
    }

    public function testMaintenancePageRequiresLogin(): void
    {
        $result = $this->get('/admin/maintenance');
        $result->assertRedirectTo('/admin/login');
    }

    public function testBackupCanBeCreatedFromAdmin(): void
    {
        $this->login();

        $result = $this->post('/admin/maintenance/backup');
        $result->assertRedirectTo('/admin/maintenance');

        $db = \Config\Database::connect();
        $backup = $db->table('backups')->orderBy('id', 'DESC')->get()->getRowArray();
        $this->assertNotNull($backup);
        $this->assertSame('success', $backup['status']);
        $this->assertGreaterThan(0, (int) $backup['size']);
        $this->assertFileExists($backup['path']);
    }

    public function testMaintenanceModeBlocksPublicAndAllowsAdmin(): void
    {
        $this->login();

        $db = \Config\Database::connect();
        $db->table('settings')->insert([
            'key'   => 'maintenance_enabled',
            'value' => '1',
            'group' => 'general',
            'type'  => 'boolean',
        ]);

        // Public frontend is blocked with 503
        $result = $this->get('/');
        $this->assertSame(503, $result->response()->getStatusCode());

        // Admin routes remain accessible while maintenance is on
        $result = $this->get('/admin/maintenance');
        $result->assertOK();
        $result->assertSee('Maintenance Mode');
    }

    public function testMaintenanceToggleRecorded(): void
    {
        $this->login();

        $result = $this->post('/admin/maintenance/toggle', ['enabled' => '1']);
        $result->assertRedirectTo('/admin/maintenance');

        $db = \Config\Database::connect();
        $setting = $db->table('settings')->where('key', 'maintenance_enabled')->get()->getRowArray();
        $this->assertNotNull($setting);
        $this->assertSame('1', $setting['value']);

        $log = $db->table('security_logs')->where('type', 'maintenance.toggle')->get()->getRowArray();
        $this->assertNotNull($log);
        $this->assertSame('warning', $log['severity']);
    }
}