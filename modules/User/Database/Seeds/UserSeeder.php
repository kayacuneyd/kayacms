<?php

namespace User\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Create admin role
        $this->db->table('roles')->insert([
            'name' => 'admin',
            'permissions' => json_encode(['*']),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $adminRoleId = $this->db->insertID();

        // Create editor role
        $this->db->table('roles')->insert([
            'name' => 'editor',
            'permissions' => json_encode([
                'dashboard.view',
                'content.view', 'content.create', 'content.edit', 'content.delete',
                'media.view', 'media.upload', 'media.delete',
                'taxonomy.view', 'taxonomy.create', 'taxonomy.edit', 'taxonomy.delete',
                'menus.view', 'menus.create', 'menus.edit', 'menus.delete',
            ]),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // Create contributor role
        $this->db->table('roles')->insert([
            'name' => 'contributor',
            'permissions' => json_encode([
                'dashboard.view',
                'content.view', 'content.create', 'content.edit',
                'media.view', 'media.upload',
                'taxonomy.view',
            ]),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // Create default admin user
        $this->db->table('users')->insert([
            'username' => 'admin',
            'email' => 'admin@kayacms.local',
            'password_hash' => password_hash('admin123', PASSWORD_DEFAULT),
            'role_id' => $adminRoleId,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
