<?php
namespace User\Models;

use App\Core\BaseModel;

class NotificationModel extends BaseModel
{
    protected $table            = 'notifications';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['recipient_id', 'type', 'title', 'body', 'url', 'is_read'];

    protected $useTimestamps = true;

    public function forUser(?int $userId)
    {
        if ($userId === null) {
            return $this->where('recipient_id', null);
        }

        return $this->where('recipient_id', $userId);
    }

    public function unread(?int $userId): int
    {
        if ($userId === null) {
            return (int) $this->where('recipient_id', null)->where('is_read', 0)->countAllResults();
        }

        return (int) $this->where('recipient_id', $userId)->where('is_read', 0)->countAllResults();
    }

    public function markAllRead(?int $userId): bool
    {
        $builder = $this;
        if ($userId === null) {
            $builder->where('recipient_id', null);
        } else {
            $builder->where('recipient_id', $userId);
        }

        return $builder->where('is_read', 0)->set(['is_read' => 1])->update();
    }
}