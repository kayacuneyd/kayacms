<?php
namespace User\Models;

use App\Core\BaseModel;

class PasswordResetModel extends BaseModel
{
    protected $table            = 'password_resets';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $useTimestamps    = true;
    protected $updatedField     = 'updated_at';
    protected $protectFields    = true;
    protected $allowedFields    = ['email', 'token', 'expires_at', 'used_at'];

    /**
     * Find a still-valid reset record for a token.
     */
    public function validToken(string $token): ?array
    {
        return $this->where('token', $token)
                    ->where('used_at', null)
                    ->where('expires_at >=', date('Y-m-d H:i:s'))
                    ->first();
    }

    /**
     * Delete all tokens for an email.
     */
    public function deleteForEmail(string $email): bool
    {
        $this->where('email', $email)->delete();

        return $this->db->affectedRows() >= 0;
    }
}