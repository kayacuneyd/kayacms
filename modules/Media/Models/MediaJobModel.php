<?php

namespace Media\Models;

use App\Core\BaseModel;

class MediaJobModel extends BaseModel
{
    protected $table          = 'media_jobs';
    protected $primaryKey     = 'id';
    protected $returnType     = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields  = true;
    protected $allowedFields  = [
        'type',
        'media_id',
        'payload',
        'status',
        'attempts',
        'max_attempts',
        'available_at',
        'error',
        'result',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Jobs currently claimable (pending and due).
     */
    public function pending(?string $type = null)
    {
        $this->where('status', 'pending')
             ->where('available_at <=', date('Y-m-d H:i:s'));

        if ($type !== null) {
            $this->where('type', $type);
        }

        return $this;
    }

    /**
     * Status breakdown for the admin panel.
     */
    public function stats(): array
    {
        $rows = $this->select('status, COUNT(*) AS total')
                     ->groupBy('status')
                     ->findAll();

        $stats = ['pending' => 0, 'processing' => 0, 'done' => 0, 'failed' => 0];

        foreach ($rows as $row) {
            $status = $row['status'] ?? '';
            if (array_key_exists($status, $stats)) {
                $stats[$status] = (int) $row['total'];
            }
        }

        return $stats;
    }
}