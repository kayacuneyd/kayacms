<?php
namespace Menu\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMenusTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INTEGER', 'unsigned' => true, 'auto_increment' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => '100'],
            'location' => ['type' => 'VARCHAR', 'constraint' => '50'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('menus');

        // Menu items table
        $this->forge->addField([
            'id' => ['type' => 'INTEGER', 'unsigned' => true, 'auto_increment' => true],
            'menu_id' => ['type' => 'INTEGER', 'unsigned' => true],
            'title' => ['type' => 'VARCHAR', 'constraint' => '200'],
            'url' => ['type' => 'VARCHAR', 'constraint' => '500', 'null' => true],
            'content_id' => ['type' => 'INTEGER', 'unsigned' => true, 'null' => true],
            'parent_id' => ['type' => 'INTEGER', 'unsigned' => true, 'null' => true],
            'sort_order' => ['type' => 'INTEGER', 'default' => 0],
            'target' => ['type' => 'VARCHAR', 'constraint' => '20', 'default' => '_self'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['menu_id', 'parent_id', 'sort_order']);
        $this->forge->createTable('menu_items');
    }

    public function down()
    {
        $this->forge->dropTable('menu_items');
        $this->forge->dropTable('menus');
    }
}
