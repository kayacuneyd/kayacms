<?php

namespace User\Models;

use CodeIgniter\Model;
use User\Entities\RoleEntity;

class RoleModel extends Model
{
    protected $table            = 'roles';
    protected $primaryKey       = 'id';
    protected $returnType       = RoleEntity::class;
    protected $protectFields    = true;
    protected $allowedFields    = ['name', 'permissions'];

    protected $useTimestamps = true;

    protected $validationRules = [
        'name' => 'required|min_length[3]|max_length[100]|is_unique[roles.name,id,{id}]',
    ];

    /**
     * Get role ID by name
     */
    public function getIdByName(string $name): ?int
    {
        $role = $this->where('name', $name)->first();

        return $role ? (int) $role->id : null;
    }
}
