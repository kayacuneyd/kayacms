<?php
namespace App\Models;

use CodeIgniter\Model;

class ContentEventModel extends Model
{
    protected $table = 'content_events';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $protectFields = true;
    protected $allowedFields = [
        'content_id',
        'event_name',
        'url',
        'referrer',
        'user_agent_hash',
        'device_type',
        'created_at',
    ];
    protected $useTimestamps = false;
}
