<?php

namespace App\Models;

use CodeIgniter\Model;

class ErrorLogModel extends Model
{
    protected $table            = 'error_logs';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'source',
        'level',
        'message',
        'url',
        'method',
        'user_agent',
        'ip_address',
        'context',
        'resolved',
        'created_at',
    ];

    protected $useTimestamps = false;

    protected $validationRules = [
        'source' => 'required|in_list[php,js]',
        'level'  => 'required|max_length[20]',
        'message' => 'required',
    ];

    public function recent(array $filters = [], int $perPage = 30, int $page = 1)
    {
        $builder = $this->orderBy('id', 'DESC');

        if (! empty($filters['source'])) {
            $builder = $builder->where('source', $filters['source']);
        }

        if (! empty($filters['level'])) {
            $builder = $builder->where('level', $filters['level']);
        }

        if (! empty($filters['resolved']) || $filters['resolved'] === '0') {
            $builder = $builder->where('resolved', (int) $filters['resolved']);
        }

        if (! empty($filters['search'])) {
            $builder = $builder->groupStart()
                ->like('message', $filters['search'])
                ->orLike('url', $filters['search'])
                ->groupEnd();
        }

        $total = (int) $builder->countAllResults(false);
        $items = $builder->findAll($perPage, ($page - 1) * $perPage);

        return [
            'items' => $items,
            'total' => $total,
            'pages' => (int) ceil($total / $perPage),
        ];
    }

    public function counts(): array
    {
        return [
            'total'      => $this->countAllResults(),
            'unresolved' => $this->where('resolved', 0)->countAllResults(),
            'php'        => $this->where('source', 'php')->countAllResults(),
            'js'         => $this->where('source', 'js')->countAllResults(),
        ];
    }
}
