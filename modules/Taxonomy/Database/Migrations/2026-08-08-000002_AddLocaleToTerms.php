<?php
namespace Taxonomy\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLocaleToTerms extends Migration
{
    public function up()
    {
        $this->forge->addColumn('terms', [
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
        $this->forge->dropColumn('terms', ['locale', 'translation_group_id']);
    }
}
