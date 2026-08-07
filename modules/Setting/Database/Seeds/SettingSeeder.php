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
            ['key' => 'items_per_page', 'value' => '10', 'group' => 'general', 'type' => 'integer'],
            ['key' => 'enable_registration', 'value' => 'true', 'group' => 'users', 'type' => 'boolean'],
        ];

        foreach ($settings as $setting) {
            $setting['created_at'] = date('Y-m-d H:i:s');
            $setting['updated_at'] = date('Y-m-d H:i:s');
            $this->db->table('cms_settings')->insert($setting);
        }
    }
}
