<?php

namespace Feature;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Setting\Models\SettingModel;

class CookieConsentTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resetServices();

        $db = \Config\Database::connect();
        $db->table('settings')->where('1=1')->delete();

        // Theme + homepage prerequisites.
        (new \Theme\Models\ThemeModel())->truncate();
        $db->table('themes')->insert(['name' => 'Default', 'slug' => 'default', 'is_active' => 1]);
    }

    private function settings(): void
    {
        $model = new SettingModel();
        $model->setSetting('site_name', 'KayaCMS', 'general', 'string');
        $model->setSetting('site_description', 'desc', 'general', 'string');
        $model->setSetting('site_default_locale', 'tr', 'general', 'string');
        $model->setSetting('site_active_locales', 'tr', 'general', 'string');
    }

    public function testBannerRenderedWhenEnabled(): void
    {
        $this->settings();
        (new SettingModel())->setSetting('cookie_consent_enabled', 'true', 'privacy', 'boolean');

        $result = $this->get('/');
        $result->assertOK();
        $result->assertSee('Cookie consent');
        $result->assertSee('id="ck-accept"');
        $result->assertSee('id="ck-decline"');
    }

    public function testBannerHiddenWhenDisabled(): void
    {
        $this->settings();
        (new SettingModel())->setSetting('cookie_consent_enabled', 'false', 'privacy', 'boolean');

        $result = $this->get('/');
        $result->assertOK();
        $result->assertDontSee('id="ck-accept"');
        $result->assertDontSee('Cookie consent');
    }

    public function testPolicyLinkRenderedWhenConfigured(): void
    {
        $this->settings();
        (new SettingModel())->setSetting('cookie_consent_enabled', 'true', 'privacy', 'boolean');
        (new SettingModel())->setSetting('privacy_policy_url', '/privacy', 'privacy', 'string');

        $result = $this->get('/');
        $result->assertOK();
        $result->assertSee('/privacy');
    }
}