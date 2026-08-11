<?php

namespace Maintenance\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBackupsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INTEGER',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'filename' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'path' => [
                'type'       => 'VARCHAR',
                'constraint' => '500',
            ],
            'size' => [
                'type'    => 'INTEGER',
                'default' => 0,
            ],
            'type' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
                'default'    => 'db',
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
                'default'    => 'success',
            ],
            'message' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('created_at');
        $this->forge->createTable('backups');
    }

    public function down()
    {
        $this->forge->dropTable('backups');
    }
}