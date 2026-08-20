<?php

namespace Setting\Controllers\Admin;

use App\Controllers\Admin\BaseAdminController;
use Setting\Models\SettingModel;

class SettingAdminController extends BaseAdminController
{
    protected SettingModel $settingModel;

    public function __construct()
    {
        $this->settingModel = new SettingModel();
    }

    public function index()
    {
        if ($redirect = $this->requirePermission('settings.view')) return $redirect;

        $data['active'] = 'settings';
        $data['title']  = 'Settings';
        $data['items']  = $this->settingModel->findAll();
        $data['settingsMap'] = $this->settingsMap();
        $data['groups'] = $this->settingGroups();
        $data['canUpdate'] = $this->can('settings.update');

        return $this->render('admin/settings/index', $data);
    }

    public function update(string $key)
    {
        if ($redirect = $this->requirePermission('settings.update')) return $redirect;

        $setting = $this->settingModel->where('key', $key)->first();

        if (! $setting || empty($setting['id'])) {
            return redirect()->to('/admin/settings')->with('error', 'Setting not found.');
        }

        $this->settingModel->update($setting['id'], [
            'value' => $this->request->getPost('value'),
        ]);

        $this->logActivity('updated', 'setting', (int) $setting['id'], "Updated setting: {$key}");

        return redirect()->to('/admin/settings')->with('success', 'Setting updated successfully.');
    }

    public function bulkUpdate()
    {
        if ($redirect = $this->requirePermission('settings.update')) return $redirect;

        $settings = $this->request->getPost('settings');

        $schema = $this->flatSettingSchema();

        foreach ($settings ?? [] as $key => $value) {
            $key = (string) $key;
            $meta = $schema[$key] ?? ['group' => 'general', 'type' => 'string'];

            if (($meta['sensitive'] ?? false) && trim((string) $value) === '') {
                continue;
            }

            $this->settingModel->setSetting($key, $value, $meta['group'], $meta['type']);
        }

        return redirect()->to('/admin/settings')->with('success', 'Settings saved successfully.');
    }

    /**
     * Send a test email using the current SMTP settings.
     */
    public function testEmail()
    {
        if ($redirect = $this->requirePermission('settings.update')) return $redirect;

        $recipient = trim((string) $this->request->getPost('test_recipient'));
        $userId = (int) session()->get('user_id');
        $user   = $userId ? (new \User\Models\UserModel())->find($userId) : null;

        if ($recipient === '' && $user) {
            $recipient = (string) $user->email;
        }

        if (! filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->with('error', 'No recipient found for test email.');
        }

        $mailer = new \User\Libraries\Mailer();
        $sent = $mailer->sendView(
            $recipient,
            'Test email from ' . $this->settingModel->getSetting('site_name', 'KayaCMS'),
            'emails/test_mail',
            ['siteName' => $this->settingModel->getSetting('site_name', 'KayaCMS')]
        );

        if ($sent) {
            $this->logActivity('sent', 'email', null, "Test email sent to {$recipient}");

            return redirect()->to('/admin/settings')->with('success', 'Test email sent to ' . $recipient . '.');
        }

        return redirect()->to('/admin/settings')->with('error', 'Test email could not be sent. Check SMTP settings.');
    }

    private function settingsMap(): array
    {
        $rows = $this->settingModel->findAll();
        $map = [];
        foreach ($rows as $row) {
            $map[$row['key']] = (string) ($row['value'] ?? '');
        }

        return $map;
    }

    private function settingGroups(): array
    {
        return [
            'Site Identity' => [
                ['key' => 'site_name', 'label' => 'Site name', 'group' => 'general'],
                ['key' => 'site_slogan', 'label' => 'Slogan', 'group' => 'general'],
                ['key' => 'site_description', 'label' => 'Site description', 'group' => 'general', 'type' => 'textarea'],
                ['key' => 'site_logo', 'label' => 'Header logo path', 'group' => 'general'],
                ['key' => 'site_footer_logo', 'label' => 'Footer logo path', 'group' => 'general'],
                ['key' => 'site_favicon', 'label' => 'Favicon path', 'group' => 'general'],
                ['key' => 'site_mascot', 'label' => 'Mascot/icon path', 'group' => 'general'],
                ['key' => 'site_phone', 'label' => 'Contact phone', 'group' => 'general'],
                ['key' => 'site_address', 'label' => 'Contact address', 'group' => 'general', 'type' => 'textarea'],
            ],
            'Email' => [
                ['key' => 'mail_protocol', 'label' => 'Mail protocol', 'group' => 'email'],
                ['key' => 'sendmail_path', 'label' => 'Sendmail path', 'group' => 'email', 'placeholder' => '/usr/sbin/sendmail'],
                ['key' => 'smtp_host', 'label' => 'SMTP host', 'group' => 'email'],
                ['key' => 'smtp_port', 'label' => 'SMTP port', 'group' => 'email', 'type' => 'integer', 'placeholder' => '587'],
                ['key' => 'smtp_crypto', 'label' => 'SMTP crypto', 'group' => 'email', 'placeholder' => 'tls'],
                ['key' => 'smtp_user', 'label' => 'SMTP username/email', 'group' => 'email'],
                ['key' => 'smtp_pass', 'label' => 'SMTP password', 'group' => 'email', 'input' => 'password', 'sensitive' => true],
                ['key' => 'smtp_from_email', 'label' => 'From email', 'group' => 'email'],
                ['key' => 'smtp_from_name', 'label' => 'From name', 'group' => 'email'],
            ],
            'SEO/GEO' => [
                ['key' => 'default_meta_title', 'label' => 'Default meta title', 'group' => 'seo'],
                ['key' => 'default_meta_description', 'label' => 'Default meta description', 'group' => 'seo', 'type' => 'textarea'],
                ['key' => 'default_og_image', 'label' => 'Default OG image', 'group' => 'seo'],
                ['key' => 'publisher_name', 'label' => 'Publisher name', 'group' => 'seo'],
                ['key' => 'publisher_logo', 'label' => 'Publisher logo', 'group' => 'seo'],
                ['key' => 'geo_region', 'label' => 'Geo region', 'group' => 'seo'],
                ['key' => 'geo_country', 'label' => 'Geo country', 'group' => 'seo'],
                ['key' => 'social_links', 'label' => 'Social links JSON', 'group' => 'seo', 'type' => 'textarea'],
            ],
            'Analytics' => [
                ['key' => 'ga_measurement_id', 'label' => 'Google Analytics Measurement ID', 'group' => 'analytics', 'placeholder' => 'G-XXXXXXXXXX'],
            ],
            'Storage' => [
                ['key' => 'storage_provider', 'label' => 'Storage provider', 'group' => 'storage', 'placeholder' => 'local'],
                ['key' => 'r2_account_id', 'label' => 'Cloudflare account ID', 'group' => 'storage'],
                ['key' => 'r2_access_key_id', 'label' => 'R2 access key ID', 'group' => 'storage'],
                ['key' => 'r2_secret_access_key', 'label' => 'R2 secret access key', 'group' => 'storage', 'input' => 'password', 'sensitive' => true],
                ['key' => 'r2_bucket', 'label' => 'R2 bucket', 'group' => 'storage'],
                ['key' => 'r2_endpoint', 'label' => 'R2 endpoint', 'group' => 'storage'],
                ['key' => 'r2_public_base_url', 'label' => 'R2 public base URL', 'group' => 'storage'],
                ['key' => 'r2_path_prefix', 'label' => 'R2 path prefix', 'group' => 'storage', 'placeholder' => 'uploads'],
            ],
            'RSS / AI Drafting' => [
                ['key' => 'rss_ai_endpoint', 'label' => 'AI endpoint', 'group' => 'rss', 'placeholder' => 'https://api.openai.com/v1/chat/completions'],
                ['key' => 'rss_ai_model', 'label' => 'AI model', 'group' => 'rss', 'placeholder' => 'gpt-4o-mini'],
                ['key' => 'rss_ai_api_key', 'label' => 'AI API key', 'group' => 'rss', 'input' => 'password', 'sensitive' => true],
                ['key' => 'rss_ai_system_prompt', 'label' => 'AI system prompt', 'group' => 'rss', 'type' => 'textarea'],
                ['key' => 'rss_ai_prompt_template', 'label' => 'AI prompt template ({title}, {summary}, {url})', 'group' => 'rss', 'type' => 'textarea'],
            ],
            'Advanced' => [
                ['key' => 'robots_txt', 'label' => 'robots.txt', 'group' => 'general', 'type' => 'textarea'],
                ['key' => 'header_scripts', 'label' => 'Header scripts', 'group' => 'general', 'type' => 'textarea'],
                ['key' => 'footer_scripts', 'label' => 'Footer scripts', 'group' => 'general', 'type' => 'textarea'],
                ['key' => 'cache_enabled', 'label' => 'Cache enabled', 'group' => 'general'],
                ['key' => 'cache_ttl', 'label' => 'Cache TTL', 'group' => 'general', 'type' => 'integer'],
                ['key' => 'cron_tasks', 'label' => 'Cron tasks', 'group' => 'system', 'placeholder' => 'media:queue,backup:create'],
            ],
        ];
    }

    private function flatSettingSchema(): array
    {
        $flat = [];
        foreach ($this->settingGroups() as $fields) {
            foreach ($fields as $field) {
                $flat[$field['key']] = [
                    'group' => $field['group'] ?? 'general',
                    'type' => $field['type'] ?? 'string',
                    'sensitive' => $field['sensitive'] ?? false,
                ];
            }
        }

        return $flat;
    }
}
