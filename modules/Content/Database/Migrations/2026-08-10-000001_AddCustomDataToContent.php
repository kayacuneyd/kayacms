<?php

namespace Content\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCustomDataToContent extends Migration
{
    public function up()
    {
        $this->forge->addColumn('content', [
            'custom_data' => [
                'type' => 'TEXT',
                'null' => true,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('content', 'custom_data');
    }
}