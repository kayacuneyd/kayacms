<?php
namespace Media\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddStorageFieldsToMedia extends Migration
{
    public function up()
    {
        $fields = [];
        if (! $this->db->fieldExists('storage_provider', 'media')) {
            $fields['storage_provider'] = [
                'type' => 'VARCHAR',
                'constraint' => 40,
                'default' => 'local',
                'after' => 'uploaded_by',
            ];
        }
        if (! $this->db->fieldExists('storage_key', 'media')) {
            $fields['storage_key'] = [
                'type' => 'VARCHAR',
                'constraint' => 600,
                'null' => true,
                'after' => 'storage_provider',
            ];
        }
        if (! $this->db->fieldExists('public_url', 'media')) {
            $fields['public_url'] = [
                'type' => 'VARCHAR',
                'constraint' => 1000,
                'null' => true,
                'after' => 'storage_key',
            ];
        }

        if ($fields) {
            $this->forge->addColumn('media', $fields);
        }
    }

    public function down()
    {
        $drop = [];
        foreach (['storage_provider', 'storage_key', 'public_url'] as $field) {
            if ($this->db->fieldExists($field, 'media')) {
                $drop[] = $field;
            }
        }

        if ($drop) {
            $this->forge->dropColumn('media', $drop);
        }
    }
}
