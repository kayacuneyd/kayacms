<?php
namespace User\Models;

use App\Core\BaseModel;

class SecurityLogModel extends BaseModel
{
    protected $table            = 'security_logs';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['severity', 'type', 'message', 'ip_address', 'user_agent', 'user_id', 'metadata'];

    protected $useTimestamps = false;

    protected $beforeInsert = ['setCreatedAt'];

    protected function setCreatedAt(array $data): array
    {
        $data['data']['created_at'] = date('Y-m-d H:i:s');

        return $data;
    }

    public function recent(int $limit = 50): array
    {
        return $this->orderBy('created_at', 'DESC')->limit($limit)->findAll();
    }

    public function countBySeverity(string $severity, ?string $since = null): int
    {
        $this->where('severity', $severity);

        if ($since) {
            $this->where('created_at >=', $since);
        }

        return (int) $this->countAllResults();
    }
}