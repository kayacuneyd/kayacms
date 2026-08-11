<?php
namespace User\Models;

use App\Core\BaseModel;

class MagicLinkModel extends BaseModel
{
    protected $table            = 'magic_links';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $useTimestamps    = true;
    protected $updatedField     = 'updated_at';
    protected $protectFields    = true;
    protected $allowedFields    = ['user_id', 'token', 'expires_at', 'used_at'];

    /**
     * Find a still-valid (unused, non-expired) magic link for a token.
     */
    public function validToken(string $token): ?array
    {
        return $this->where('token', $token)
                    ->where('used_at', null)
                    ->where('expires_at >=', date('Y-m-d H:i:s'))
                    ->first();
    }

    /**
     * Invalidate every outstanding token issued to a user.
     */
    public function invalidateForUser(int $userId): bool
    {
        $this->where('user_id', $userId)->where('used_at', null)->set('used_at', date('Y-m-d H:i:s'))->update();

        return true;
    }
}