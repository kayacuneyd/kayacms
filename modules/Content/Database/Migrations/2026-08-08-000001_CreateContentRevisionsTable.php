<?php

namespace Content\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateContentRevisionsTable extends Migration
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
            'version' => [
                'type'     => 'INTEGER',
                'unsigned' => true,
                'default'  => 1,
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'slug' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'body' => [
                'type' => 'TEXT',
            ],
            'excerpt' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'content_type' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'default'    => 'article',
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
                'default'    => 'draft',
            ],
            'featured_image' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            'meta_title' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            'meta_description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'meta_keywords' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'canonical_url' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            'published_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'created_by' => [
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
        $this->forge->addKey(['content_id', 'version']);
        $this->forge->createTable('content_revisions');
    }

    public function down()
    {
        $this->forge->dropTable('content_revisions');
    }
}
