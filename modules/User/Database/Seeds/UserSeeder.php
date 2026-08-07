<?php
namespace User\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Create admin role
        $this->db->table('cms_roles')->insert([
            'name' => 'admin',
            'permissions' => json_encode(['*']), // All permissions
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $adminRoleId = $this->db->insertID();

        // Create editor role
        $this->db->table('cms_roles')->insert([
            'name' => 'editor',
            'permissions' => json_encode(['content.create', 'content.edit', 'content.delete', 'media.upload']),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // Create default admin user
        $this->db->table('cms_users')->insert([
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
