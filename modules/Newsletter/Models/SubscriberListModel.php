<?php
namespace Newsletter\Models;

use CodeIgniter\Model;

class SubscriberListModel extends Model
{
    protected $table = 'newsletter_subscriber_lists';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $protectFields = true;
    protected $allowedFields = ['subscriber_id', 'list_id', 'created_at'];
    protected $useTimestamps = false;

    public function attach(int $subscriberId, int $listId): void
    {
        $exists = $this->where('subscriber_id', $subscriberId)->where('list_id', $listId)->first();
        if (! $exists) {
            $this->insert([
                'subscriber_id' => $subscriberId,
                'list_id' => $listId,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }
}
