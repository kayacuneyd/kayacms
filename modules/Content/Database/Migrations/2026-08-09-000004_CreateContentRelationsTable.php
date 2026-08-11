<?php

namespace Content\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateContentRelationsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INTEGER',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'source_id' => [
                'type'     => 'INTEGER',
                'unsigned' => true,
            ],
            'target_id' => [
                'type'     => 'INTEGER',
                'unsigned' => true,
            ],
            'type' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'default'    => 'related',
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['source_id', 'target_id']);
        $this->forge->createTable('content_relations');
    }

    public function down()
    {
        $this->forge->dropTable('content_relations');
    }
}