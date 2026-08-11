<?php

namespace Contact\Models;

use CodeIgniter\Model;

class ContactFormModel extends Model
{
    protected $table = 'contact_forms';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['name', 'slug', 'fields', 'settings', 'is_active'];
    protected $useTimestamps = true;

    public function find($id = null)
    {
        $result = parent::find($id);
        return $this->decode($result);
    }

    public function findAll(?int $limit = null, int $offset = 0)
    {
        $results = parent::findAll($limit, $offset);
        foreach ($results as &$row) {
            $row = $this->decode($row);
        }
        return $results;
    }

    public function first()
    {
        $result = parent::first();
        return $this->decode($result);
    }

    public function findBySlug(string $slug)
    {
        $row = $this->where('slug', $slug)->where('is_active', 1)->first();
        return $this->decode($row);
    }

    public function insert($data = null, bool $returnID = true)
    {
        $data = $this->encode($data);
        return parent::insert($data, $returnID);
    }

    public function update($id = null, $data = null): bool
    {
        $data = $this->encode($data);
        return parent::update($id, $data);
    }

    private function decode($row)
    {
        if (! is_array($row)) {
            return $row;
        }
        if (is_string($row['fields'] ?? '')) {
            $row['fields'] = json_decode($row['fields'], true);
        }
        if (is_string($row['settings'] ?? '')) {
            $row['settings'] = json_decode($row['settings'], true);
        }
        return $row;
    }

    private function encode($data)
    {
        if (! is_array($data)) {
            return $data;
        }
        if (isset($data['fields']) && is_array($data['fields'])) {
            $data['fields'] = json_encode($data['fields'], JSON_UNESCAPED_UNICODE);
        }
        if (isset($data['settings']) && is_array($data['settings'])) {
            $data['settings'] = json_encode($data['settings'], JSON_UNESCAPED_UNICODE);
        }
        return $data;
    }
}
