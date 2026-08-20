<?php
namespace Newsletter\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddQualityFieldsToNewsletterSubscribers extends Migration
{
    public function up()
    {
        $this->forge->addColumn('newsletter_subscribers', [
            'email_domain' => [
                'type' => 'VARCHAR',
                'constraint' => 190,
                'null' => true,
            ],
            'quality_status' => [
                'type' => 'VARCHAR',
                'constraint' => 40,
                'default' => 'unreviewed',
            ],
            'quality_score' => [
                'type' => 'INTEGER',
                'default' => 0,
            ],
            'quality_reasons' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'suppressed_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'reviewed_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('newsletter_subscribers', [
            'email_domain',
            'quality_status',
            'quality_score',
            'quality_reasons',
            'suppressed_at',
            'reviewed_at',
        ]);
    }
}
