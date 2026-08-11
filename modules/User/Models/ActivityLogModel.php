<?php

namespace User\Models;

use CodeIgniter\Model;

class ActivityLogModel extends Model
{
    protected $table            = 'activity_logs';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'username',
        'action',
        'entity_type',
        'entity_id',
        'description',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $useTimestamps = false;

    /**
     * Get recent activity logs with optional filtering
     */
    public function getRecent(int $limit = 50, ?string $entityType = null, ?string $action = null): array
    {
        $builder = $this->orderBy('created_at', 'DESC');

        if ($entityType) {
            $builder->where('entity_type', $entityType);
        }

        if ($action) {
            $builder->where('action', $action);
        }

        return $builder->findAll($limit);
    }

    /**
     * Get activity summary counts grouped by action
     */
    public function getSummary(): array
    {
        return $this->select('action, COUNT(*) as total')
                    ->groupBy('action')
                    ->orderBy('total', 'DESC')
                    ->findAll();
    }
}
