<?php

namespace App\Models;

use CodeIgniter\Model;

class ContentBookmarkModel extends Model
{
    protected $table = 'content_bookmarks';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $protectFields = true;
    protected $allowedFields = ['user_id', 'content_id'];
    protected $useTimestamps = true;
    protected $useSoftDeletes = false;

    public function isBookmarked(int $userId, int $contentId): bool
    {
        return (bool) $this->where('user_id', $userId)
            ->where('content_id', $contentId)
            ->first();
    }

    public function toggle(int $userId, int $contentId): bool
    {
        $existing = $this->where('user_id', $userId)
            ->where('content_id', $contentId)
            ->first();

        if ($existing) {
            $this->delete((int) $existing['id']);

            return false;
        }

        $this->insert([
            'user_id' => $userId,
            'content_id' => $contentId,
        ]);

        return true;
    }
}
