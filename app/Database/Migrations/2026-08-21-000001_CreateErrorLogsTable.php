<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateErrorLogsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'source' => [
                'type' => 'VARCHAR',
                'constraint' => '10',
                'comment' => 'php or js',
            ],
            'level' => [
                'type' => 'VARCHAR',
                'constraint' => '20',
            ],
            'message' => [
                'type' => 'TEXT',
            ],
            'url' => [
                'type' => 'VARCHAR',
                'constraint' => '500',
                'null' => true,
            ],
            'method' => [
                'type' => 'VARCHAR',
                'constraint' => '10',
                'null' => true,
            ],
            'user_agent' => [
                'type' => 'VARCHAR',
                'constraint' => '500',
                'null' => true,
            ],
            'ip_address' => [
                'type' => 'VARCHAR',
                'constraint' => '45',
                'null' => true,
            ],
            'context' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'resolved' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('source');
        $this->forge->addKey('level');
        $this->forge->addKey('resolved');
        $this->forge->addKey('created_at');
        $this->forge->createTable('error_logs', true);
    }

    public function down()
    {
        $this->forge->dropTable('error_logs', true);
    }
}
