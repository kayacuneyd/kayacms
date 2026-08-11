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
 *   ];
 *
 * Values are persisted as JSON in the `themes.config` column and exposed to
 * theme views as `$theme_config` (merged with defaults).
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
                'default' => (string) ($def['default'] ?? ''),
                'options' => is_array($def['options'] ?? null) ? array_values($def['options']) : [],
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
            $resolved[$field['key']] = $field['default'];
        }

        $saved = $this->saved($row);
        foreach ($saved as $key => $value) {
            $resolved[$key] = (string) $value;
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