<?php

namespace Content\Libraries;

use Content\Models\ContentTypeSchemaModel;

/**
 * CustomFields — definition, collection and validation of per-content-type
 * user-defined fields.
 *
 * A content type's schema is a JSON list of field definitions:
 *
 *   [
 *     {"name":"color","label":"Color","type":"select","options":["red","blue"],"required":true},
 *     {"name":"price","label":"Price","type":"number"},
 *     {"name":"notes","label":"Notes","type":"textarea"}
 *   ]
 *
 * Supported types: text, textarea, number, email, url, select, checkbox,
 * date, datetime, toggle. Values are stored as JSON in content.custom_data.
 */
class CustomFields
{
    /** Field input types supported by the generator. */
    public const TYPES = ['text', 'textarea', 'number', 'email', 'url', 'select', 'checkbox', 'date', 'datetime', 'toggle'];

    protected ContentTypeSchemaModel $schemaModel;

    public function __construct(?ContentTypeSchemaModel $schemaModel = null)
    {
        $this->schemaModel = $schemaModel ?? new ContentTypeSchemaModel();
    }

    /**
     * Schema (list of field definitions) for a content type.
     */
    public function schemaFor(string $contentType): array
    {
        return $this->schemaModel->getSchema($contentType) ?? [];
    }

    /**
     * Collect `custom` values from raw request input into a JSON-ready map,
     * applying defaults and normalizing checkbox/number fields.
     */
    public function collect(array $input, string $contentType): array
    {
        $data      = [];
        $submitted = $input['custom'] ?? [];
        $submitted = is_array($submitted) ? $submitted : [];

        foreach ($this->schemaFor($contentType) as $field) {
            $name = $field['name'] ?? '';

            if ($name === '') {
                continue;
            }

            $type    = $field['type'] ?? 'text';
            $default = $field['default'] ?? null;

            if ($type === 'checkbox' || $type === 'toggle') {
                $data[$name] = ! empty($submitted[$name] ?? null) ? 1 : 0;
                continue;
            }

            $value = $submitted[$name] ?? $default;

            if ($type === 'number') {
                $data[$name] = ($value === '' || $value === null) ? null : (float) $value;
                continue;
            }

            $data[$name] = $value === null ? null : (string) $value;
        }

        return $data;
    }

    /**
     * Validate collected custom values against the schema requirements.
     * Returns a flat list of human readable error messages.
     */
    public function validate(array $data, string $contentType): array
    {
        $errors = [];

        foreach ($this->schemaFor($contentType) as $field) {
            $name     = $field['name'] ?? '';
            $label    = $field['label'] ?? $name;
            $type     = $field['type'] ?? 'text';
            $required = ! empty($field['required']);
            $value    = $data[$name] ?? null;

            if ($name === '') {
                continue;
            }

            if ($type === 'toggle' || $type === 'checkbox') {
                continue; // always has a value (0/1)
            }

            if ($required && ($value === null || $value === '')) {
                $errors[] = "{$label} is required.";

                continue;
            }

            if ($value === '' || $value === null) {
                continue;
            }

            if ($type === 'email' && ! filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $errors[] = "{$label} must be a valid email address.";
            }

            if ($type === 'url' && ! filter_var($value, FILTER_VALIDATE_URL)) {
                $errors[] = "{$label} must be a valid URL.";
            }

            if ($type === 'select' && ! empty($field['options']) && ! in_array((string) $value, (array) $field['options'], true)) {
                $errors[] = "{$label} has an invalid selection.";
            }
        }

        return $errors;
    }

    /**
     * Field values for rendering prefilled inputs from stored custom_data
     * (merges schema defaults so a plain array is always returned).
     */
    public function merge(string $contentType, ?array $stored = null, array $submitted = null): array
    {
        $values = [];

        foreach ($this->schemaFor($contentType) as $field) {
            $name                 = $field['name'] ?? '';
            $values[$name]        = $field['default'] ?? null;

            if ($name === '') {
                continue;
            }

            if (is_array($submitted) && array_key_exists($name, $submitted)) {
                $values[$name] = $submitted[$name];
            } elseif (is_array($stored) && array_key_exists($name, $stored)) {
                $values[$name] = $stored[$name];
            }
        }

        return $values;
    }
}