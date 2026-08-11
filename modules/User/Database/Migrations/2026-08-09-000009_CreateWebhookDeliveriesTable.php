<?php

namespace User\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateWebhookDeliveriesTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INTEGER',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'webhook_id' => [
                'type'     => 'INTEGER',
                'unsigned' => true,
            ],
            'event' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'payload' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
                'default'    => 'pending',
            ],
            'response_code' => [
                'type'    => 'INTEGER',
                'null'    => true,
            ],
            'response_body' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'attempts' => [
                'type'    => 'INTEGER',
                'default' => 0,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('status');
        $this->forge->addKey('webhook_id');
        $this->forge->createTable('webhook_deliveries');
    }

    public function down()
    {
        $this->forge->dropTable('webhook_deliveries');
    }
}