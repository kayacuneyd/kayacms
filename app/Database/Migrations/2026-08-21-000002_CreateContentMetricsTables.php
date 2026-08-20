<?php
namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateContentMetricsTables extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INTEGER', 'unsigned' => true, 'auto_increment' => true],
            'content_id' => ['type' => 'INTEGER', 'unsigned' => true],
            'views_total' => ['type' => 'INTEGER', 'unsigned' => true, 'default' => 0],
            'views_imported' => ['type' => 'INTEGER', 'unsigned' => true, 'default' => 0],
            'views_internal' => ['type' => 'INTEGER', 'unsigned' => true, 'default' => 0],
            'avg_read_seconds' => ['type' => 'INTEGER', 'unsigned' => true, 'default' => 0],
            'last_viewed_at' => ['type' => 'DATETIME', 'null' => true],
            'source_snapshot' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('content_id');
        $this->forge->createTable('content_metrics');

        $this->forge->addField([
            'id' => ['type' => 'INTEGER', 'unsigned' => true, 'auto_increment' => true],
            'content_id' => ['type' => 'INTEGER', 'unsigned' => true],
            'metric_date' => ['type' => 'DATE'],
            'views' => ['type' => 'INTEGER', 'unsigned' => true, 'default' => 0],
            'source' => ['type' => 'VARCHAR', 'constraint' => 80, 'default' => 'internal'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('content_id');
        $this->forge->addKey('metric_date');
        $this->forge->createTable('daily_content_metrics');

        $this->forge->addField([
            'id' => ['type' => 'INTEGER', 'unsigned' => true, 'auto_increment' => true],
            'content_id' => ['type' => 'INTEGER', 'unsigned' => true, 'null' => true],
            'event_name' => ['type' => 'VARCHAR', 'constraint' => 60, 'default' => 'page_view'],
            'url' => ['type' => 'VARCHAR', 'constraint' => 700, 'null' => true],
            'referrer' => ['type' => 'VARCHAR', 'constraint' => 700, 'null' => true],
            'user_agent_hash' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'device_type' => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('content_id');
        $this->forge->addKey('event_name');
        $this->forge->createTable('content_events');
    }

    public function down()
    {
        $this->forge->dropTable('content_events');
        $this->forge->dropTable('daily_content_metrics');
        $this->forge->dropTable('content_metrics');
    }
}
