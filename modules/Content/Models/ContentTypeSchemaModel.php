<?php

namespace Content\Models;

use CodeIgniter\Model;

/**
 * ContentTypeSchemaModel — stores the JSON field schema for each content
 * type. Each schema is a list of field definitions used to render dynamic
 * input controls in the admin form and to read/write content.custom_data.
 */
class ContentTypeSchemaModel extends Model
{
    protected $table            = 'content_type_schemas';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['content_type', 'schema'];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'content_type' => 'required|alpha_dash|max_length[50]',
        'schema'       => 'required|max_length[65535]',
    ];

    /**
     * Get the decoded schema for a content type, or null when absent.
     */
    public function getSchema(string $contentType): ?array
    {
        $row = $this->where('content_type', $contentType)->first();

        if (! $row || empty($row['schema'])) {
            return null;
        }

        $decoded = json_decode($row['schema'], true);

        return is_array($decoded) ? $decoded : null;
    }

    /**
     * Upsert the schema for a content type. Returns the row id.
     */
    public function setSchema(string $contentType, array $schema): int
    {
        $existing = $this->where('content_type', $contentType)->first();

        $payload = [
            'content_type' => $contentType,
            'schema'       => json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ];

        if ($existing) {
            $this->update((int) $existing['id'], $payload);

            return (int) $existing['id'];
        }

        $this->insert($payload);

        return (int) $this->getInsertID();
    }

    /**
     * All content types currently registered in the content table plus the
     * built-in defaults, each with (possibly null) schema.
     */
    public function allWithTypes(): array
    {
        $rows = $this->orderBy('content_type', 'ASC')->findAll();

        return array_map(static function (array $row) {
            $row['fields'] = json_decode((string) $row['schema'], true) ?: [];

            return $row;
        }, $rows);
    }
}