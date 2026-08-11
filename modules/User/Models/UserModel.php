<?php
namespace User\Models;

use App\Core\BaseModel;
use User\Entities\UserEntity;

class UserModel extends BaseModel
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $returnType       = UserEntity::class;
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'username',
        'email',
        'password',
        'password_hash',
        'role_id',
        'status',
        'totp_secret',
    ];

    protected $useTimestamps = true;

    protected $validationRules = [
        'username' => 'required|min_length[3]|max_length[100]|is_unique[users.username,id,{id}]',
        'email'    => 'required|valid_email|is_unique[users.email,id,{id}]',
        'status'   => 'in_list[active,inactive,banned]',
    ];

    protected $validationMessages = [
        'username' => [
            'required'   => 'Username is required',
            'is_unique'  => 'Username already exists',
        ],
        'email' => [
            'required'   => 'Email is required',
            'valid_email' => 'Please provide a valid email',
            'is_unique'  => 'Email already registered',
        ],
    ];

    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPassword'];

    /**
     * Hash password before saving
     */
    protected function hashPassword(array $data)
    {
        if (isset($data['data']['password']) && !empty($data['data']['password'])) {
            $data['data']['password_hash'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
            unset($data['data']['password']);
        }
        return $data;
    }

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?UserEntity
    {
        return $this->where('email', $email)->first();
    }

    /**
     * Find user by username
     */
    public function findByUsername(string $username): ?UserEntity
    {
        return $this->where('username', $username)->first();
    }

    /**
     * Get active users only
     */
    public function active()
    {
        return $this->where('status', 'active');
    }

    /**
     * Get users with role information
     */
    public function withRole()
    {
        return $this->join('roles', 'roles.id = users.role_id', 'left')
                    ->select('users.*, roles.name as role_name');
    }
}
