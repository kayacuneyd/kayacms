<?php
namespace Theme\Models;

use CodeIgniter\Model;

class ThemeModel extends Model
{
    protected $table = 'themes';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['name', 'slug', 'is_active', 'config'];
    protected $useTimestamps = true;

    public function getActive()
    {
        return $this->where('is_active', 1)->first();
    }

    public function activate(int $id): bool
    {
        $this->update(null, ['is_active' => 0]); // Deactivate all
        return $this->update($id, ['is_active' => 1]); // Activate selected
    }
}
