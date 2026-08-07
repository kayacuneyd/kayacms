<?php
namespace Theme\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateThemesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INTEGER', 'unsigned' => true, 'auto_increment' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => '100'],
            'slug' => ['type' => 'VARCHAR', 'constraint' => '100', 'unique' => true],
            'is_active' => ['type' => 'INTEGER', 'constraint' => 1, 'default' => 0],
            'config' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('themes');
    }

    public function down()
    {
        $this->forge->dropTable('themes');
    }
}
