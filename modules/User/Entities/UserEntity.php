<?php
namespace User\Entities;

use CodeIgniter\Entity\Entity;

class UserEntity extends Entity
{
    protected $attributes = [
        'id'            => null,
        'username'      => null,
        'email'         => null,
        'password_hash' => null,
        'role_id'       => null,
        'status'        => 'active',
        'created_at'    => null,
        'updated_at'    => null,
        'deleted_at'    => null,
    ];

    protected $casts = [
        'id'      => 'integer',
        'role_id' => 'integer',
    ];

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    // Don't expose password in JSON
    protected $hidden = ['password_hash'];

    /**
     * Set password with automatic hashing
     */
    public function setPassword(string $password)
    {
        $this->attributes['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
        return $this;
    }

    /**
     * Verify password
     */
    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->attributes['password_hash']);
    }
}
