<?php

namespace Content\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFeaturedToContent extends Migration
{
    public function up()
    {
        $this->forge->addColumn('content', [
            'is_featured' => [
                'type'    => 'BOOLEAN',
                'default' => 0,
                'null'    => false,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('content', 'is_featured');
    }
}