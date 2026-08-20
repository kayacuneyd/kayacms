<?php
namespace Content\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPublishingSeoFieldsToContent extends Migration
{
    public function up()
    {
        $this->forge->addColumn('content', [
            'source_system' => [
                'type' => 'VARCHAR',
                'constraint' => 40,
                'null' => true,
            ],
            'source_id' => [
                'type' => 'VARCHAR',
                'constraint' => 80,
                'null' => true,
            ],
            'source_url' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
                'null' => true,
            ],
            'canonical_url' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
                'null' => true,
            ],
            'seo_data' => [
                'type' => 'TEXT',
                'null' => true,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('content', [
            'source_system',
            'source_id',
            'source_url',
            'canonical_url',
            'seo_data',
        ]);
    }
}
