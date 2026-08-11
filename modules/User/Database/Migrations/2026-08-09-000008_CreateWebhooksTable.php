<?php

namespace User\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWebhooksTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INTEGER',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'url' => [
                'type'       => 'VARCHAR',
                'constraint' => '500',
            ],
            'event' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'secret' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            'is_active' => [
                'type'    => 'BOOLEAN',
                'default' => 1,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('event');
        $this->forge->createTable('webhooks');
    }

    public function down()
    {
        $this->forge->dropTable('webhooks');
    }
}