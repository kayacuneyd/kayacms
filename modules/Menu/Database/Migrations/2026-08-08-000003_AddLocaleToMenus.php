<?php
namespace Menu\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLocaleToMenus extends Migration
{
    public function up()
    {
        $this->forge->addColumn('menus', [
            'locale' => [
                'type'       => 'VARCHAR',
                'constraint' => '10',
                'default'    => 'tr',
                'after'      => 'id',
            ],
            'translation_group_id' => [
                'type'       => 'VARCHAR',
                'constraint' => '32',
                'null'       => true,
                'after'      => 'locale',
            ],
        ]);

        $this->forge->addColumn('menu_items', [
            'locale' => [
                'type'       => 'VARCHAR',
                'constraint' => '10',
                'default'    => 'tr',
                'after'      => 'id',
            ],
        ]);

        $this->forge->addKey('menus.locale');
        $this->forge->addKey('menus.translation_group_id');
        $this->forge->addKey('menu_items.locale');
    }

    public function down()
    {
        $this->forge->dropColumn('menus', ['locale', 'translation_group_id']);
        $this->forge->dropColumn('menu_items', ['locale']);
    }
}
