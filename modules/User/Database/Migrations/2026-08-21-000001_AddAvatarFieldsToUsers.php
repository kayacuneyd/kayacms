<?php
namespace User\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAvatarFieldsToUsers extends Migration
{
    public function up()
    {
        $fields = [
            'avatar_path' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'after'      => 'status',
            ],
            'avatar_updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'avatar_path',
            ],
        ];

        $this->forge->addColumn('users', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('users', ['avatar_path', 'avatar_updated_at']);
    }
}
