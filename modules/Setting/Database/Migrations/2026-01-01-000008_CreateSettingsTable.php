<?php
namespace Setting\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSettingsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INTEGER', 'unsigned' => true, 'auto_increment' => true],
            'key' => ['type' => 'VARCHAR', 'constraint' => '100', 'unique' => true],
            'value' => ['type' => 'TEXT', 'null' => true],
            'group' => ['type' => 'VARCHAR', 'constraint' => '50', 'default' => 'general'],
            'type' => ['type' => 'VARCHAR', 'constraint' => '20', 'default' => 'string'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('key');
        $this->forge->addKey('group');
        $this->forge->createTable('settings');
    }

    public function down()
    {
        $this->forge->dropTable('settings');
    }
}
