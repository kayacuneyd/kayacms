<?php
namespace Newsletter\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateNewsletterTables extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'INTEGER', 'unsigned' => true, 'auto_increment' => true],
            'email' => ['type' => 'VARCHAR', 'constraint' => 255],
            'name' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'subscribed'],
            'source' => ['type' => 'VARCHAR', 'constraint' => 100, 'default' => 'site'],
            'consent_text' => ['type' => 'TEXT', 'null' => true],
            'consent_ip' => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true],
            'consented_at' => ['type' => 'DATETIME', 'null' => true],
            'unsubscribe_token' => ['type' => 'VARCHAR', 'constraint' => 80],
            'meta' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('email');
        $this->forge->addKey('status');
        $this->forge->addKey('unsubscribe_token');
        $this->forge->createTable('newsletter_subscribers');

        $this->forge->addField([
            'id' => ['type' => 'INTEGER', 'unsigned' => true, 'auto_increment' => true],
            'name' => ['type' => 'VARCHAR', 'constraint' => 160],
            'slug' => ['type' => 'VARCHAR', 'constraint' => 160],
            'description' => ['type' => 'TEXT', 'null' => true],
            'is_default' => ['type' => 'INTEGER', 'constraint' => 1, 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('slug');
        $this->forge->createTable('newsletter_lists');

        $this->forge->addField([
            'id' => ['type' => 'INTEGER', 'unsigned' => true, 'auto_increment' => true],
            'subscriber_id' => ['type' => 'INTEGER', 'unsigned' => true],
            'list_id' => ['type' => 'INTEGER', 'unsigned' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('subscriber_id');
        $this->forge->addKey('list_id');
        $this->forge->createTable('newsletter_subscriber_lists');

        $this->forge->addField([
            'id' => ['type' => 'INTEGER', 'unsigned' => true, 'auto_increment' => true],
            'subject' => ['type' => 'VARCHAR', 'constraint' => 255],
            'preheader' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'body_html' => ['type' => 'TEXT'],
            'body_text' => ['type' => 'TEXT', 'null' => true],
            'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'draft'],
            'provider' => ['type' => 'VARCHAR', 'constraint' => 40, 'default' => 'smtp'],
            'list_id' => ['type' => 'INTEGER', 'unsigned' => true, 'null' => true],
            'scheduled_at' => ['type' => 'DATETIME', 'null' => true],
            'sent_at' => ['type' => 'DATETIME', 'null' => true],
            'created_by' => ['type' => 'INTEGER', 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('status');
        $this->forge->createTable('newsletter_campaigns');

        $this->forge->addField([
            'id' => ['type' => 'INTEGER', 'unsigned' => true, 'auto_increment' => true],
            'campaign_id' => ['type' => 'INTEGER', 'unsigned' => true],
            'subscriber_id' => ['type' => 'INTEGER', 'unsigned' => true],
            'email' => ['type' => 'VARCHAR', 'constraint' => 255],
            'status' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'pending'],
            'attempts' => ['type' => 'INTEGER', 'unsigned' => true, 'default' => 0],
            'max_attempts' => ['type' => 'INTEGER', 'unsigned' => true, 'default' => 3],
            'available_at' => ['type' => 'DATETIME', 'null' => true],
            'sent_at' => ['type' => 'DATETIME', 'null' => true],
            'error' => ['type' => 'TEXT', 'null' => true],
            'provider_message_id' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('campaign_id');
        $this->forge->addKey('subscriber_id');
        $this->forge->addKey('status');
        $this->forge->createTable('newsletter_queue');
    }

    public function down()
    {
        $this->forge->dropTable('newsletter_queue');
        $this->forge->dropTable('newsletter_campaigns');
        $this->forge->dropTable('newsletter_subscriber_lists');
        $this->forge->dropTable('newsletter_lists');
        $this->forge->dropTable('newsletter_subscribers');
    }
}
