<?php

namespace Content\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateVirtualPagesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INTEGER',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'slug' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'unique'     => true,
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
            ],
            'handler' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'default'    => 'template',
            ],
            'payload' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'active',
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
        $this->forge->addKey('slug', false, true);
        $this->forge->addKey('status');
        $this->forge->createTable('virtual_pages');
    }

    public function down()
    {
        $this->forge->dropTable('virtual_pages');
    }
}