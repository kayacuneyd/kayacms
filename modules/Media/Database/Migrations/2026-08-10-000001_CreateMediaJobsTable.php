<?php

namespace Media\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMediaJobsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INTEGER',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
            ],
            'media_id' => [
                'type'       => 'INTEGER',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'payload' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'default'    => 'pending',
            ],
            'attempts' => [
                'type'       => 'INTEGER',
                'constraint' => 11,
                'unsigned'   => true,
                'default'    => 0,
            ],
            'max_attempts' => [
                'type'       => 'INTEGER',
                'constraint' => 11,
                'unsigned'   => true,
                'default'    => 3,
            ],
            'available_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'error' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'result' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('status', false, false, 'media_jobs_status_idx');
        $this->forge->addKey('type', false, false, 'media_jobs_type_idx');
        $this->forge->addKey(['status', 'available_at'], false, false, 'media_jobs_claim_idx');
        $this->forge->addKey('media_id', false, false, 'media_jobs_media_idx');
        $this->forge->createTable('media_jobs');
    }

    public function down()
    {
        $this->forge->dropTable('media_jobs');
    }
}