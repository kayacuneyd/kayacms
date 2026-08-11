<?php

namespace Content\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateContentCollectionItemsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INTEGER',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'collection_id' => [
                'type'     => 'INTEGER',
                'unsigned' => true,
            ],
            'content_id' => [
                'type'     => 'INTEGER',
                'unsigned' => true,
            ],
            'sort_order' => [
                'type'    => 'INTEGER',
                'default' => 0,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['collection_id', 'content_id']);
        $this->forge->createTable('content_collection_items');
    }

    public function down()
    {
        $this->forge->dropTable('content_collection_items');
    }
}