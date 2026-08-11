<?php

namespace Media\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFilePathToMedia extends Migration
{
    public function up()
    {
        $fields = [
            'file_path' => [
                'type'       => 'VARCHAR',
                'constraint' => '500',
                'null'       => true,
            ],
        ];
        $this->forge->addColumn('media', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('media', 'file_path');
    }
}