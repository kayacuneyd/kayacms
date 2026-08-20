<?php
namespace Media\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDerivativeFieldsToMedia extends Migration
{
    public function up()
    {
        $this->forge->addColumn('media', [
            'caption' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'credit' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'source_url' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
                'null' => true,
            ],
            'derivatives' => [
                'type' => 'TEXT',
                'null' => true,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('media', ['caption', 'credit', 'source_url', 'derivatives']);
    }
}
