<?php

namespace Theme\Libraries;

use Theme\Models\ThemeModel;
use App\Libraries\QueryCache;

/**
 * ThemeConfig — a declarative per-theme configuration layer that makes the
 * theme "developable" without touching PHP.
 *
 * A theme may ship an optional `config.php` inside its view directory
 * (e.g. `app/Views/themes/{slug}/config.php`). That file returns an array of
 * field definitions:
 *
 *   return [
 *       ['key' => 'brand_color', 'label' => 'Brand Color', 'type' => 'text',   'default' => '#2563eb'],
 *       ['key' => 'show_search', 'label' => 'Show Search', 'type' => 'toggle', 'default' => '1'],
 *       ['key' => 'footer_text', 'label' => 'Footer Text', 'type' => 'textarea','default' => ''],
 *       ['key' => 'services', 'label' => 'Services', 'type' => 'repeater',
 *        'fields' => [
 *            ['name' => 'title', 'label' => 'Title', 'type' => 'text'],
 *            ['name' => 'desc',  'label' => 'Description', 'type' => 'textarea'],
 *        ]],
 *   ];
 *
 * Values are persisted as JSON in the `themes.config` column and exposed to
 * theme views as `$theme_config` (merged with defaults). `repeater` fields
 * resolve to an array of row arrays (each row keyed by its sub-field `name`).
 */
class ThemeConfig
{
    protected ThemeModel $themeModel;

    public function __construct(?ThemeModel $themeModel = null)
    {
        $this->themeModel = $themeModel ?? new ThemeModel();
    }

    /**
     * Field schema declared by a theme (from its config.php), if any.
     */
    public function schema(string $themeSlug): array
    {
        $slug = $this->safeSlug($themeSlug);
        $file = APPPATH . 'Views/themes/' . $slug . '/config.php';

        if (! is_file($file)) {
            return [];
        }

        $defs = require $file;

        if (! is_array($defs)) {
            return [];
        }

        $normalized = [];
        foreach ($defs as $def) {
            if (! is_array($def) || empty($def['key'])) {
                continue;
            }

            $normalized[] = [
                'key'     => (string) $def['key'],
                'label'   => (string) ($def['label'] ?? ucfirst((string) $def['key'])),
                'type'    => (string) ($def['type'] ?? 'text'),
                'default' => $def['default'] ?? '',
                'options' => is_array($def['options'] ?? null) ? array_values($def['options']) : [],
                'fields'  => $this->normalizeRepeaterFields($def['fields'] ?? []),
            ];
        }

        return $normalized;
    }

    /**
     * Normalize a `repeater` field's sub-field definitions.
     *
     * @return list<array{name: string, label: string, type: string, options: list<string>}>
     */
    protected function normalizeRepeaterFields(mixed $fields): array
    {
        $normalized = [];

        if (! is_array($fields)) {
            return $normalized;
        }

        foreach ($fields as $sub) {
            if (! is_array($sub) || empty($sub['name'])) {
                continue;
            }

            $normalized[] = [
                'name'    => (string) $sub['name'],
                'label'   => (string) ($sub['label'] ?? ucfirst((string) $sub['name'])),
                'type'    => (string) ($sub['type'] ?? 'text'),
                'options' => is_array($sub['options'] ?? null) ? array_values($sub['options']) : [],
            ];
        }

        return $normalized;
    }

    /**
     * Full config resolved for a theme row: saved values over defaults.
     */
    public function resolve(?array $themeRow): array
    {
        $row = $themeRow ?? $this->themeModel->getActive();
        $slug = $row['slug'] ?? 'default';

        $resolved = [];
        foreach ($this->schema($slug) as $field) {
            $resolved[$field['key']] = $field['type'] === 'repeater' ? [] : (string) $field['default'];
        }

        $saved = $this->saved($row);
        foreach ($saved as $key => $value) {
            if (! array_key_exists($key, $resolved)) {
                continue;
            }

            $resolved[$key] = is_array($resolved[$key])
                ? (is_array($value) ? $value : [])
                : (string) $value;
        }

        return $resolved;
    }

    /**
     * Persist theme config values (only known schema keys are stored).
     */
    public function save(int $themeId, array $values): bool
    {
        $theme = $this->themeModel->find($themeId);

        if (! $theme) {
            return false;
        }

        $slug    = $theme['slug'] ?? 'default';
        $schema  = $this->schema($slug);
        $allowed = [];
        foreach ($schema as $field) {
            $allowed[$field['key']] = $field;
        }

        $clean = [];
        foreach ($allowed as $key => $field) {
            if ($field['type'] === 'repeater') {
                $clean[$key] = $this->cleanRepeater($values[$key] ?? [], $field['fields'] ?? []);
                continue;
            }

            if (! array_key_exists($key, $values)) {
                continue;
            }

            $value = $values[$key];

            if ($field['type'] === 'toggle') {
                $clean[$key] = $value !== '' && $value !== '0' && $value !== null && $value !== false ? '1' : '0';
                continue;
            }

            $clean[$key] = (string) $value;
        }

        $json = json_encode($clean, JSON_UNESCAPED_UNICODE) ?: '{}';

        QueryCache::instance()->forget('theme');

        return $this->themeModel->update($themeId, ['config' => $json]) !== false;
    }

    /**
     * Clean a submitted `repeater` value into a list of rows keyed by the
     * repeater's sub-field names. Rows with no non-empty values are dropped.
     *
     * @param array<int, array<string, mixed>>|mixed $value
     */
    protected function cleanRepeater(mixed $value, array $fields): array
    {
        $rows = [];

        if (! is_array($value)) {
            return $rows;
        }

        // Support both a flat list of rows and a keyed ["__rows__" => [...]].
        $candidate = isset($value['__rows__']) && is_array($value['__rows__'])
            ? $value['__rows__']
            : $value;
        $candidate = array_values($candidate);

        foreach ($candidate as $idx => $row) {
            if (! is_array($row)) {
                continue;
            }

            $cleanRow = [];
            foreach ($fields as $sub) {
                $name  = $sub['name'];
                $has   = array_key_exists($name, $row);
                $val   = $has ? $row[$name] : '';

                if ($sub['type'] === 'checkbox' || $sub['type'] === 'toggle') {
                    $cleanRow[$name] = ! empty($val) ? 1 : 0;
                    continue;
                }

                $cleanRow[$name] = $has ? (string) $val : '';
            }

            if (array_filter($cleanRow, static fn ($v) => $v !== '') !== []) {
                $rows[] = $cleanRow;
            }
        }

        return $rows;
    }

    /**
     * Decoded saved config for a theme row ([] when empty/broken).
     */
    public function saved(?array $themeRow): array
    {
        $raw  = (string) ($themeRow['config'] ?? '');
        $data = json_decode($raw, true);

        return is_array($data) ? $data : [];
    }

    protected function safeSlug(string $slug): string
    {
        $slug = preg_replace('/[^a-z0-9-_]/', '', $slug) ?: 'default';

        return $slug;
    }
}