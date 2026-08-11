<?php

namespace Media\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFolderAndDimensionsToMedia extends Migration
{
    public function up()
    {
        $fields = [
            'folder_id' => [
                'type'     => 'INTEGER',
                'unsigned' => true,
                'null'     => true,
            ],
            'width' => [
                'type'     => 'INTEGER',
                'unsigned' => true,
                'null'     => true,
            ],
            'height' => [
                'type'     => 'INTEGER',
                'unsigned' => true,
                'null'     => true,
            ],
        ];

        $this->forge->addColumn('media', $fields);
        $this->forge->addKey('folder_id', false, false, 'media_folder_idx');
    }

    public function down()
    {
        $this->forge->dropColumn('media', 'folder_id');
        $this->forge->dropColumn('media', 'width');
        $this->forge->dropColumn('media', 'height');
    }
}