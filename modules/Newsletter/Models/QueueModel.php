<?php
namespace Newsletter\Models;

use CodeIgniter\Model;

class QueueModel extends Model
{
    protected $table = 'newsletter_queue';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $protectFields = true;
    protected $allowedFields = [
        'campaign_id', 'subscriber_id', 'email', 'status', 'attempts', 'max_attempts',
        'available_at', 'sent_at', 'error', 'provider_message_id',
    ];
    protected $useTimestamps = true;

    public function pending(int $limit = 25): array
    {
        return $this->where('status', 'pending')
            ->groupStart()
                ->where('available_at', null)
                ->orWhere('available_at <=', date('Y-m-d H:i:s'))
            ->groupEnd()
            ->orderBy('id', 'ASC')
            ->findAll($limit);
    }
}
