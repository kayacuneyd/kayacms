<?php

namespace Content\Controllers\Admin;

use App\Controllers\Admin\BaseAdminController;
use Content\Libraries\CustomFields;
use Content\Models\ContentTypeSchemaModel;

class SchemaAdminController extends BaseAdminController
{
    protected ContentTypeSchemaModel $schemaModel;

    public function __construct()
    {
        $this->schemaModel = new ContentTypeSchemaModel();
    }

    public function index()
    {
        if ($redirect = $this->requirePermission('content.edit')) {
            return $redirect;
        }

        $data['active'] = 'content_schemas';
        $data['title']  = 'Custom Fields';
        $data['schemas'] = $this->schemaModel->allWithTypes();
        $data['types']   = $this->getAllContentTypes();

        return $this->render('admin/content/schemas/index', $data);
    }

    public function edit(string $contentType)
    {
        if ($redirect = $this->requirePermission('content.edit')) {
            return $redirect;
        }

        $contentType = urldecode($contentType);

        $data['active']      = 'content';
        $data['title']       = 'Edit Custom Fields';
        $data['contentType'] = $contentType;
        $data['schema']      = $this->schemaModel->getSchema($contentType) ?? [];
        $data['types']       = $this->getAllContentTypes();
        $data['fieldTypes']  = CustomFields::TYPES;

        return $this->render('admin/content/schemas/edit', $data);
    }

    public function store(string $contentType)
    {
        if ($redirect = $this->requirePermission('content.edit')) {
            return $redirect;
        }

        $contentType = urldecode($contentType);

        $fields = $this->request->getPost('fields');
        $fields = is_array($fields) ? array_values($fields) : [];

        $schema = [];

        foreach ($fields as $field) {
            $name = trim((string) ($field['name'] ?? ''));
            $type = (string) ($field['type'] ?? 'text');

            if ($name === '') {
                continue;
            }

            if (! in_array($type, CustomFields::TYPES, true)) {
                $type = 'text';
            }

            $definition = [
                'name'     => $name,
                'label'    => trim((string) ($field['label'] ?? $name)),
                'type'     => $type,
                'required' => ! empty($field['required']),
            ];

            $default         = (string) ($field['default'] ?? '');
            if ($default !== '') {
                $definition['default'] = ($type === 'number') ? (float) $default : $default;
            }

            if ($type === 'select') {
                $options = $field['options'] ?? '';
                $options = is_array($options) ? $options : preg_split('/[\\r\\n,]+/', (string) $options);

                $definition['options'] = array_values(array_filter(array_map(
                    static fn ($o) => trim((string) $o),
                    $options
                )));
            }

            $schema[] = $definition;
        }

        if (! $this->schemaModel->setSchema($contentType, $schema)) {
            return redirect()->back()->withInput()->with('error', implode(', ', $this->schemaModel->errors()));
        }

        $this->logActivity('updated', 'content_schema', 0, "Updated custom fields for content type: {$contentType}");

        return redirect()->to('/admin/content/schemas')->with('success', 'Custom fields saved.');
    }

    public function delete(string $contentType)
    {
        if ($redirect = $this->requirePermission('content.delete')) {
            return $redirect;
        }

        $contentType = urldecode($contentType);
        $row = $this->schemaModel->where('content_type', $contentType)->first();

        if ($row) {
            $this->schemaModel->delete((int) $row['id']);
        }

        return redirect()->to('/admin/content/schemas')->with('success', 'Custom fields removed.');
    }

    /**
     * All registered content types (unified list from content + schemas).
     */
    protected function getAllContentTypes(): array
    {
        $content = (new \Content\Models\ContentModel())
            ->select('content_type')
            ->distinct()
            ->findAll();

        $derived = array_column($content, 'content_type');
        $schemaRows = $this->schemaModel->select('content_type')->findAll();
        $schemaTypes = array_column($schemaRows, 'content_type');

        $types = array_values(array_unique(array_merge(
            ['article', 'page'],
            $derived,
            $schemaTypes
        )));

        sort($types);

        return $types;
    }
}