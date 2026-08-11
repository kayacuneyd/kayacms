<?php

namespace User\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTotpAndLoginSecurityToUsers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('users', [
            'totp_secret' => [
                'type'       => 'VARCHAR',
                'constraint' => '32',
                'null'       => true,
                'after'      => 'password_hash',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'totp_secret');
    }
}