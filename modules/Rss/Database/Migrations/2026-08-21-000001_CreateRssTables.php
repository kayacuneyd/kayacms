<?php

namespace Rss\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRssTables extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('rss_sources') && $this->db->tableExists('rss_items')) {
            return;
        }

        $this->forge->addField([
            'id' => ['type' => 'INTEGER', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 190],
            'feed_url' => ['type' => 'TEXT'],
            'country' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'language' => ['type' => 'VARCHAR', 'constraint' => 16, 'default' => 'en'],
            'source_type' => ['type' => 'VARCHAR', 'constraint' => 80, 'default' => 'media'],
            'is_active' => ['type' => 'INTEGER', 'constraint' => 1, 'default' => 1],
            'last_fetched_at' => ['type' => 'DATETIME', 'null' => true],
            'last_error' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('is_active');
        $this->forge->createTable('rss_sources', true);

        $this->forge->addField([
            'id' => ['type' => 'INTEGER', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'source_id' => ['type' => 'INTEGER', 'constraint' => 11, 'unsigned' => true],
            'guid_hash' => ['type' => 'VARCHAR', 'constraint' => 64],
            'guid' => ['type' => 'TEXT', 'null' => true],
            'original_title' => ['type' => 'VARCHAR', 'constraint' => 500],
            'original_summary' => ['type' => 'TEXT', 'null' => true],
            'original_url' => ['type' => 'TEXT'],
            'published_at' => ['type' => 'DATETIME', 'null' => true],
            'status' => ['type' => 'VARCHAR', 'constraint' => 24, 'default' => 'new'],
            'ai_suggestion' => ['type' => 'TEXT', 'null' => true],
            'created_content_id' => ['type' => 'INTEGER', 'constraint' => 11, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('source_id');
        $this->forge->addKey('status');
        $this->forge->addUniqueKey('guid_hash');
        $this->forge->createTable('rss_items', true);
    }

    public function down()
    {
        $this->forge->dropTable('rss_items', true);
        $this->forge->dropTable('rss_sources', true);
    }
}
