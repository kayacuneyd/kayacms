<?php

namespace Contact\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ContactFormSeeder extends Seeder
{
    public function run()
    {
        $exists = $this->db->table('contact_forms')->where('slug', 'contact')->countAllResults();

        if ($exists > 0) {
            return;
        }

        $fields = json_encode([
            ['name' => 'name', 'label' => 'Full Name', 'type' => 'text', 'required' => true, 'options' => []],
            ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true, 'options' => []],
            ['name' => 'message', 'label' => 'Message', 'type' => 'textarea', 'required' => true, 'options' => []],
        ], JSON_UNESCAPED_UNICODE);

        $settings = json_encode(['notify_email' => 'admin@kayacms.local'], JSON_UNESCAPED_UNICODE);

        $this->db->table('contact_forms')->insert([
            'name' => 'General Contact',
            'slug' => 'contact',
            'fields' => $fields,
            'settings' => $settings,
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
