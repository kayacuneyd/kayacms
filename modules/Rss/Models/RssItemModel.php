<?php

namespace Rss\Models;

use CodeIgniter\Model;

class RssItemModel extends Model
{
    protected $table = 'rss_items';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'source_id',
        'guid_hash',
        'guid',
        'original_title',
        'original_summary',
        'original_url',
        'published_at',
        'status',
        'ai_suggestion',
        'created_content_id',
    ];
    protected $useTimestamps = true;

    public function withSource()
    {
        return $this->select('rss_items.*, rss_sources.name AS source_name, rss_sources.country, rss_sources.language, content.title AS created_content_title, content.status AS created_content_status, content.slug AS created_content_slug')
            ->join('rss_sources', 'rss_sources.id = rss_items.source_id', 'left')
            ->join('content', 'content.id = rss_items.created_content_id', 'left');
    }
}
