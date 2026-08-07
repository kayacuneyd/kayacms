<?php
namespace Setting\Models;

use CodeIgniter\Model;

class SettingModel extends Model
{
    protected $table = 'settings';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['key', 'value', 'group', 'type'];
    protected $useTimestamps = true;

    public function getSetting(string $key, $default = null)
    {
        $setting = $this->where('key', $key)->first();
        return $setting ? $this->castValue($setting['value'], $setting['type']) : $default;
    }

    public function setSetting(string $key, $value, string $group = 'general', string $type = 'string'): bool
    {
        $existing = $this->where('key', $key)->first();
        $data = ['key' => $key, 'value' => (string)$value, 'group' => $group, 'type' => $type];

        if ($existing) {
            return $this->update($existing['id'], $data);
        }
        return $this->insert($data) !== false;
    }

    public function getByGroup(string $group): array
    {
        $settings = $this->where('group', $group)->findAll();
        $result = [];
        foreach ($settings as $setting) {
            $result[$setting['key']] = $this->castValue($setting['value'], $setting['type']);
        }
        return $result;
    }

    private function castValue($value, string $type)
    {
        return match($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int)$value,
            'float' => (float)$value,
            'json' => json_decode($value, true),
            default => $value,
        };
    }
}
