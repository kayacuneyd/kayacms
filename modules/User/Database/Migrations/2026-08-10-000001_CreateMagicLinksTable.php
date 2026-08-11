<?php

namespace User\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMagicLinksTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INTEGER',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type'       => 'INTEGER',
                'unsigned'   => true,
            ],
            'token' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'expires_at' => [
                'type' => 'DATETIME',
            ],
            'used_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id', false, false, 'magic_links_user_idx');
        $this->forge->addKey('token');
        $this->forge->addKey('expires_at');
        $this->forge->createTable('magic_links');
    }

    public function down()
    {
        $this->forge->dropTable('magic_links');
    }
}