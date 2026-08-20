<?php

namespace Rss\Models;

use CodeIgniter\Model;

class RssSourceModel extends Model
{
    protected $table = 'rss_sources';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'name',
        'feed_url',
        'country',
        'language',
        'source_type',
        'is_active',
        'last_fetched_at',
        'last_error',
    ];
    protected $useTimestamps = true;
}
