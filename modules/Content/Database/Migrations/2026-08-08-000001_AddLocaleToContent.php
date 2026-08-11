<?php
namespace Content\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLocaleToContent extends Migration
{
    public function up()
    {
        $this->forge->addColumn('content', [
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

        $this->forge->addKey('locale');
        $this->forge->addKey('translation_group_id');
    }

    public function down()
    {
        $this->forge->dropColumn('content', ['locale', 'translation_group_id']);
    }
}
