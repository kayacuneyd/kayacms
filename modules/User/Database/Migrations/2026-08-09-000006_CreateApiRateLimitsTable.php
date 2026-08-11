<?php

namespace User\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateApiRateLimitsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INTEGER',
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'identifier' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'route' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            'hit_count' => [
                'type'    => 'INTEGER',
                'default' => 1,
            ],
            'window_started_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'last_request_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('identifier');
        $this->forge->createTable('api_rate_limits');
    }

    public function down()
    {
        $this->forge->dropTable('api_rate_limits');
    }
}