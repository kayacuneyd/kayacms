<?php

namespace Theme\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ThemeSeeder extends Seeder
{
    public function run()
    {
        $themes = [
            ['name' => 'Default', 'slug' => 'default', 'is_active' => 1],
            ['name' => 'Minimal', 'slug' => 'minimal', 'is_active' => 0],
            ['name' => 'Landing', 'slug' => 'landing', 'is_active' => 0],
            ['name' => 'Corporate', 'slug' => 'corporate', 'is_active' => 0],
        ];

        foreach ($themes as $theme) {
            $existing = $this->db->table('themes')->where('slug', $theme['slug'])->countAllResults();

            if ($existing === 0) {
                $this->db->table('themes')->insert(array_merge($theme, ['config' => null]));
            }
        }
    }
}
