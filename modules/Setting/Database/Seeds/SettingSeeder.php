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
            ['key' => 'site_slogan', 'value' => '', 'group' => 'general', 'type' => 'string'],
            ['key' => 'site_url', 'value' => 'http://localhost:8080', 'group' => 'general', 'type' => 'string'],
            ['key' => 'site_logo', 'value' => '', 'group' => 'general', 'type' => 'string'],
            ['key' => 'site_footer_logo', 'value' => '', 'group' => 'general', 'type' => 'string'],
            ['key' => 'site_favicon', 'value' => '', 'group' => 'general', 'type' => 'string'],
            ['key' => 'site_mascot', 'value' => '', 'group' => 'general', 'type' => 'string'],
            ['key' => 'site_phone', 'value' => '', 'group' => 'general', 'type' => 'string'],
            ['key' => 'site_address', 'value' => '', 'group' => 'general', 'type' => 'textarea'],
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
            ['key' => 'mail_protocol', 'value' => 'smtp', 'group' => 'email', 'type' => 'string'],
            ['key' => 'sendmail_path', 'value' => '/usr/sbin/sendmail', 'group' => 'email', 'type' => 'string'],
            ['key' => 'smtp_from_email', 'value' => '', 'group' => 'email', 'type' => 'string'],
            ['key' => 'smtp_from_name', 'value' => '', 'group' => 'email', 'type' => 'string'],
            ['key' => 'default_meta_title', 'value' => '', 'group' => 'seo', 'type' => 'string'],
            ['key' => 'default_meta_description', 'value' => '', 'group' => 'seo', 'type' => 'textarea'],
            ['key' => 'default_og_image', 'value' => '', 'group' => 'seo', 'type' => 'string'],
            ['key' => 'publisher_name', 'value' => '', 'group' => 'seo', 'type' => 'string'],
            ['key' => 'publisher_logo', 'value' => '', 'group' => 'seo', 'type' => 'string'],
            ['key' => 'geo_region', 'value' => '', 'group' => 'seo', 'type' => 'string'],
            ['key' => 'geo_country', 'value' => '', 'group' => 'seo', 'type' => 'string'],
            ['key' => 'social_links', 'value' => '{}', 'group' => 'seo', 'type' => 'textarea'],
            ['key' => 'ga_measurement_id', 'value' => '', 'group' => 'analytics', 'type' => 'string'],
            ['key' => 'storage_provider', 'value' => 'local', 'group' => 'storage', 'type' => 'string'],
            ['key' => 'r2_account_id', 'value' => '', 'group' => 'storage', 'type' => 'string'],
            ['key' => 'r2_access_key_id', 'value' => '', 'group' => 'storage', 'type' => 'string'],
            ['key' => 'r2_secret_access_key', 'value' => '', 'group' => 'storage', 'type' => 'string'],
            ['key' => 'r2_bucket', 'value' => '', 'group' => 'storage', 'type' => 'string'],
            ['key' => 'r2_endpoint', 'value' => '', 'group' => 'storage', 'type' => 'string'],
            ['key' => 'r2_public_base_url', 'value' => '', 'group' => 'storage', 'type' => 'string'],
            ['key' => 'r2_path_prefix', 'value' => 'uploads', 'group' => 'storage', 'type' => 'string'],
            ['key' => 'rss_ai_endpoint', 'value' => 'https://api.openai.com/v1/chat/completions', 'group' => 'rss', 'type' => 'string'],
            ['key' => 'rss_ai_model', 'value' => 'gpt-4o-mini', 'group' => 'rss', 'type' => 'string'],
            ['key' => 'rss_ai_api_key', 'value' => '', 'group' => 'rss', 'type' => 'string'],
            ['key' => 'rss_ai_system_prompt', 'value' => '', 'group' => 'rss', 'type' => 'textarea'],
            ['key' => 'rss_ai_prompt_template', 'value' => '', 'group' => 'rss', 'type' => 'textarea'],
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
