<?php

namespace Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use User\Libraries\MagicLink;
use User\Models\MagicLinkModel;
use User\Models\RoleModel;
use User\Models\UserModel;

class MagicLinkTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resetServices();

        $db = \Config\Database::connect();
        $db->table('roles')->truncate();
        $db->table('users')->where('1=1')->delete();
        $db->table('magic_links')->where('1=1')->delete();
        $db->table('login_attempts')->where('1=1')->delete();
        $db->table('security_logs')->where('1=1')->delete();

        (new \Setting\Models\SettingModel())->setSetting('magic_link_enabled', 'true', 'users', 'boolean');

        $roleModel = new RoleModel();
        $roleModel->insert(['name' => 'admin', 'permissions' => '["*"]']);
        $roleId = (int) $roleModel->getInsertID();

        (new UserModel())->insert([
            'username' => 'magicuser',
            'email' => 'magic@kayacms.local',
            'password_hash' => password_hash('test123', PASSWORD_DEFAULT),
            'role_id' => $roleId,
            'status' => 'active',
        ]);
    }

    public function testMagicLinkFormLoads(): void
    {
        $result = $this->get('/admin/magic-link');

        $result->assertOK();
        $result->assertSee('Passwordless Sign In');
        $result->assertSee('Send Sign-In Link');
    }

    public function testMagicLinkRequestCreatesPendingLink(): void
    {
        $this->mockEmail();

        $result = $this->post('/admin/magic-link', [
            'email' => 'magic@kayacms.local',
        ]);

        $result->assertRedirect();
        $result->assertSessionHas('success');

        $link = (new MagicLinkModel())->where('user_id >', 0)->orderBy('id', 'DESC')->first();

        $this->assertNotNull($link);
        $this->assertGreaterThan(time(), strtotime((string) $link['expires_at']));
        $this->assertNull($link['used_at']);
    }

    public function testMagicLinkRequestDoesNotEnumerateEmails(): void
    {
        $this->mockEmail();

        $known = $this->post('/admin/magic-link', ['email' => 'magic@kayacms.local']);
        $unknown = $this->post('/admin/magic-link', ['email' => 'nobody@kayacms.local']);

        $this->assertTrue($known->isRedirect());
        $this->assertTrue($unknown->isRedirect());
        $unknown->assertSessionHas('success');
    }

    public function testMagicLinkConsumeLogsUserInAndIsOneTime(): void
    {
        $magic = new MagicLink();
        $user = (new UserModel())->findByEmail('magic@kayacms.local');
        $token = $magic->issue((int) $user->id);
        $link = $magic->linkFor($token);
        $tokenFromUrl = basename(parse_url($link, PHP_URL_PATH));

        $result = $this->get('/admin/magic-link/' . $tokenFromUrl);

        $result->assertRedirectTo('/admin/dashboard');
        $result->assertSessionHas('user_id');

        // A second click must fail (single-use).
        $db = \Config\Database::connect();
        $used = $db->table('magic_links')->where('token', $token)->get()->getRowArray();
        $this->assertNotNull($used['used_at']);

        $again = $this->get('/admin/magic-link/' . $tokenFromUrl);
        $again->assertRedirectTo('/admin/login');
        $again->assertSessionHas('error');
    }

    public function testMagicLinkConsumeRejectsInvalidToken(): void
    {
        $result = $this->get('/admin/magic-link/not-a-real-token');

        $result->assertRedirectTo('/admin/login');
        $result->assertSessionHas('error');
    }

    public function testMagicLinkIssueInvalidatesPreviousLinks(): void
    {
        $magic = new MagicLink();
        $user = (new UserModel())->findByEmail('magic@kayacms.local');

        $magic->issue((int) $user->id);
        $token = $magic->issue((int) $user->id);

        $count = (new MagicLinkModel())
            ->where('user_id', (int) $user->id)
            ->where('used_at', null)
            ->countAllResults();

        $this->assertSame(1, $count);

        $link = (new MagicLinkModel())->where('token', $token)->first();
        $this->assertNotNull($link);
    }

    public function testMagicLinkSettingToggleDisablesFeature(): void
    {
        (new \Setting\Models\SettingModel())->setSetting('magic_link_enabled', 'false', 'users', 'boolean');

        $result = $this->get('/admin/magic-link');

        $result->assertRedirectTo('/admin/login');
        $result->assertSessionHas('error');
    }
}