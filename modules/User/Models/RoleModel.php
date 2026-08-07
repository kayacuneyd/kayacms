<?php
namespace User\Models;

use CodeIgniter\Model;

class RoleModel extends Model
{
    protected $table            = 'roles';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = ['name', 'permissions'];

    protected $useTimestamps = true;

    protected $validationRules = [
        'name' => 'required|min_length[3]|max_length[100]|is_unique[roles.name,id,{id}]',
    ];

    /**
     * Get permissions as array
     */
    public function getPermissionsAttribute(?string $permissions): array
    {
        return $permissions ? json_decode($permissions, true) : [];
    }

    /**
     * Set permissions from array
     */
    public function setPermissionsAttribute(array $permissions): string
    {
        return json_encode($permissions);
    }
}
