<?php

namespace Content\Models;

use App\Core\BaseModel;

class ContentRevisionModel extends BaseModel
{
    protected $table         = 'content_revisions';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'content_id',
        'version',
        'title',
        'slug',
        'body',
        'excerpt',
        'content_type',
        'status',
        'featured_image',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'canonical_url',
        'published_at',
        'created_by',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = '';

    /**
     * Get next version number for a content
     */
    public function getNextVersion(int $contentId): int
    {
        $row = $this->where('content_id', $contentId)
                    ->selectMax('version')
                    ->first();

        return ((int) ($row['version'] ?? 0)) + 1;
    }

    /**
     * Get all revisions for a content
     */
    public function getRevisions(int $contentId): array
    {
        return $this->where('content_id', $contentId)
                    ->orderBy('version', 'DESC')
                    ->findAll();
    }

    /**
     * Save a snapshot of the current content
     */
    public function createRevision(array $content, int $userId): int
    {
        $revision = [
            'content_id'       => $content['id'],
            'version'          => $this->getNextVersion($content['id']),
            'title'            => $content['title'],
            'slug'             => $content['slug'],
            'body'             => $content['body'],
            'excerpt'          => $content['excerpt'] ?? null,
            'content_type'     => $content['content_type'],
            'status'           => $content['status'],
            'featured_image'   => $content['featured_image'] ?? null,
            'meta_title'       => $content['meta_title'] ?? null,
            'meta_description' => $content['meta_description'] ?? null,
            'meta_keywords'    => $content['meta_keywords'] ?? null,
            'canonical_url'    => $content['canonical_url'] ?? null,
            'published_at'     => $content['published_at'] ?? null,
            'created_by'       => $userId,
        ];

        return $this->insert($revision);
    }

    /**
     * Find a specific revision
     */
    public function findRevision(int $contentId, int $revisionId): ?array
    {
        return $this->where('id', $revisionId)
                    ->where('content_id', $contentId)
                    ->first();
    }
}
