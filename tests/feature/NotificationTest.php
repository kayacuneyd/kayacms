<?php

namespace Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use User\Libraries\Notifications;
use User\Models\RoleModel;
use User\Models\UserModel;

class NotificationTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resetServices();

        $db = \Config\Database::connect();
        $db->table('roles')->truncate();
        $db->table('users')->where('1=1')->delete();

        $roleModel = new RoleModel();
        $roleModel->insert(['name' => 'admin', 'permissions' => '["*"]']);
        $roleId = (int) $roleModel->getInsertID();

        (new UserModel())->insert([
            'username' => 'notifman',
            'email' => 'notify@kayacms.local',
            'password_hash' => password_hash('test123', PASSWORD_DEFAULT),
            'role_id' => $roleId,
            'status' => 'active',
        ]);
    }

    public function testNotificationsPageRequiresLogin(): void
    {
        $result = $this->get('/admin/notifications');
        $result->assertRedirectTo('/admin/login');
    }

    public function testNotificationsPageLoadsForLoggedInUser(): void
    {
        $user = (new UserModel())->findByEmail('notify@kayacms.local');

        $this->post('/admin/auth/attempt', [
            'email' => 'notify@kayacms.local',
            'password' => 'test123',
        ]);
        $this->session = $_SESSION;

        \User\Libraries\Notifications::notify((int) $user->id, 'test', 'Test Notification', 'Body text');

        $result = $this->get('/admin/notifications');
        $result->assertOK();
        $result->assertSee('Test Notification');
    }
}