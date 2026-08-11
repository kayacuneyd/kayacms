<?php

namespace Maintenance\Models;

use CodeIgniter\Model;

class BackupModel extends Model
{
    protected $table      = 'backups';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['filename', 'path', 'size', 'type', 'status', 'message'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = '';
}