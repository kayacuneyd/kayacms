<?php
namespace User\Models;

use App\Core\BaseModel;

class LoginAttemptModel extends BaseModel
{
    protected $table            = 'login_attempts';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['email', 'ip_address', 'user_agent', 'success'];

    protected $useTimestamps = false;

    protected $beforeInsert = ['setCreatedAt'];

    protected function setCreatedAt(array $data): array
    {
        $data['data']['created_at'] = date('Y-m-d H:i:s');

        return $data;
    }

    public function recentFailures(string $email, string $ip, int $window = 900): int
    {
        return (int) $this->where('email', $email)
            ->where('ip_address', $ip)
            ->where('success', 0)
            ->where('created_at >=', date('Y-m-d H:i:s', time() - $window))
            ->countAllResults();
    }
}