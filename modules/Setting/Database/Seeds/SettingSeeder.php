<?php
namespace Setting\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run()
    {
        $settings = [
            ['key' => 'site_name', 'value' => 'KayaCMS', 'group' => 'general', 'type' => 'string'],
            ['key' => 'site_description', 'value' => 'A modular headless CMS', 'group' => 'general', 'type' => 'string'],
            ['key' => 'site_url', 'value' => 'http://localhost:8080', 'group' => 'general', 'type' => 'string'],
            ['key' => 'site_default_locale', 'value' => 'tr', 'group' => 'general', 'type' => 'string'],
            ['key' => 'site_active_locales', 'value' => 'tr,en', 'group' => 'general', 'type' => 'string'],
            ['key' => 'items_per_page', 'value' => '10', 'group' => 'general', 'type' => 'integer'],
            ['key' => 'admin_email', 'value' => 'admin@kayacms.local', 'group' => 'general', 'type' => 'string'],
            ['key' => 'cache_enabled', 'value' => 'false', 'group' => 'general', 'type' => 'boolean'],
            ['key' => 'cache_ttl', 'value' => '3600', 'group' => 'general', 'type' => 'integer'],
            ['key' => 'robots_txt', 'value' => "User-agent: *\nDisallow: /admin/\nDisallow: /admin\nAllow: /\nSitemap: http://localhost:8080/sitemap.xml\n", 'group' => 'general', 'type' => 'string'],
            ['key' => 'smtp_host', 'value' => '', 'group' => 'email', 'type' => 'string'],
            ['key' => 'smtp_user', 'value' => '', 'group' => 'email', 'type' => 'string'],
            ['key' => 'smtp_pass', 'value' => '', 'group' => 'email', 'type' => 'string'],
            ['key' => 'smtp_port', 'value' => '587', 'group' => 'email', 'type' => 'integer'],
            ['key' => 'smtp_crypto', 'value' => 'tls', 'group' => 'email', 'type' => 'string'],
            ['key' => 'enable_registration', 'value' => 'true', 'group' => 'users', 'type' => 'boolean'],
            ['key' => 'magic_link_enabled', 'value' => 'true', 'group' => 'users', 'type' => 'boolean'],
            ['key' => 'cookie_consent_enabled', 'value' => 'true', 'group' => 'privacy', 'type' => 'boolean'],
            ['key' => 'privacy_policy_url', 'value' => '', 'group' => 'privacy', 'type' => 'string'],
            ['key' => 'header_scripts', 'value' => '', 'group' => 'general', 'type' => 'textarea'],
            ['key' => 'footer_scripts', 'value' => '', 'group' => 'general', 'type' => 'textarea'],
            ['key' => 'cron_token', 'value' => '', 'group' => 'system', 'type' => 'string'],
            ['key' => 'cron_tasks', 'value' => 'media:queue,backup:create', 'group' => 'system', 'type' => 'string'],
        ];

        foreach ($settings as $setting) {
            $setting['created_at'] = date('Y-m-d H:i:s');
            $setting['updated_at'] = date('Y-m-d H:i:s');
            
            $existing = $this->db->table('settings')->where('key', $setting['key'])->get()->getRow();
            if ($existing) {
                $this->db->table('settings')->where('key', $setting['key'])->update($setting);
            } else {
                $this->db->table('settings')->insert($setting);
            }
        }
    }
}
