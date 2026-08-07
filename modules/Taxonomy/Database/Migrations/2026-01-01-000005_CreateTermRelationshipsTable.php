<?php
namespace Taxonomy\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTermRelationshipsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INTEGER',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'content_id' => [
                'type'     => 'INTEGER',
                'unsigned' => true,
            ],
            'term_id' => [
                'type'     => 'INTEGER',
                'unsigned' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('content_id');
        $this->forge->addKey('term_id');
        $this->forge->createTable('term_relationships');
    }

    public function down()
    {
        $this->forge->dropTable('term_relationships');
    }
}
