<?php

namespace User\Entities;

use CodeIgniter\Entity\Entity;

class RoleEntity extends Entity
{
    protected $datamap = [];
    protected $dates   = ['created_at', 'updated_at'];
    protected $casts   = [
        'id'          => 'integer',
        'permissions' => 'json',
    ];

    /**
     * Check if role has a specific permission
     */
    public function hasPermission(string $permission): bool
    {
        $permissions = $this->permissions ?? [];

        if (in_array('*', $permissions, true)) {
            return true;
        }

        // Wildcard support: content.* matches content.create
        foreach ($permissions as $allowed) {
            if ($allowed === $permission) {
                return true;
            }

            $allowedPattern = rtrim($allowed, '.*');
            if (str_ends_with($allowed, '.*') && str_starts_with($permission, $allowedPattern . '.')) {
                return true;
            }
        }

        return false;
    }
}
