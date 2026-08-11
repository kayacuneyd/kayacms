<?php

namespace Media\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddThumbnailToMedia extends Migration
{
    public function up()
    {
        $fields = [
            'thumbnail_path' => [
                'type'       => 'VARCHAR',
                'constraint' => '500',
                'null'       => true,
            ],
        ];
        $this->forge->addColumn('media', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('media', 'thumbnail_path');
    }
}
