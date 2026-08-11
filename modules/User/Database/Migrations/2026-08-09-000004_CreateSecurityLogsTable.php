<?php

namespace User\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSecurityLogsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INTEGER',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'severity' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
                'default'    => 'info',
            ],
            'type' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'message' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'ip_address' => [
                'type'       => 'VARCHAR',
                'constraint' => '45',
                'null'       => true,
            ],
            'user_agent' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            'user_id' => [
                'type'     => 'INTEGER',
                'unsigned' => true,
                'null'     => true,
            ],
            'metadata' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['type', 'created_at']);
        $this->forge->addKey('severity');
        $this->forge->createTable('security_logs');
    }

    public function down()
    {
        $this->forge->dropTable('security_logs');
    }
}