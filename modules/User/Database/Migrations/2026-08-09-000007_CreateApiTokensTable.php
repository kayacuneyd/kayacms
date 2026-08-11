<?php

namespace User\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateApiTokensTable extends Migration
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
                'type'     => 'INTEGER',
                'unsigned' => true,
                'null'     => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'token_hash' => [
                'type'       => 'VARCHAR',
                'constraint' => '64',
            ],
            'scopes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'expires_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'last_used_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('token_hash');
        $this->forge->addKey('user_id');
        $this->forge->createTable('api_tokens');
    }

    public function down()
    {
        $this->forge->dropTable('api_tokens');
    }
}