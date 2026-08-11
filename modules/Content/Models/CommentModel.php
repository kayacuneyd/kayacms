<?php

namespace Content\Models;

use CodeIgniter\Model;

class CommentModel extends Model
{
    protected $table            = 'comments';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'content_id',
        'parent_id',
        'author_name',
        'author_email',
        'author_url',
        'body',
        'status',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $validationRules = [
        'content_id'   => 'required|integer',
        'author_name'  => 'required|string|max_length[255]',
        'author_email' => 'required|valid_email|max_length[255]',
        'body'         => 'required|string',
        'status'       => 'required|in_list[pending,approved,spam]',
    ];

    public function getApprovedByContent(int $contentId): array
    {
        return $this->where('content_id', $contentId)
            ->where('status', 'approved')
            ->orderBy('created_at', 'ASC')
            ->findAll();
    }

    public function getPendingCount(): int
    {
        return (int) $this->where('status', 'pending')->countAllResults();
    }

    public function buildTree(array $comments, ?int $parentId = null): array
    {
        $tree = [];

        foreach ($comments as $comment) {
            if (($comment['parent_id'] ?? null) === $parentId) {
                $comment['children'] = $this->buildTree($comments, $comment['id']);
                $tree[] = $comment;
            }
        }

        return $tree;
    }
}
