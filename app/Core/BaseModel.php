<?php
namespace App\Core;

use CodeIgniter\Model;

/**
 * BaseModel
 *
 * Shared model base with common configurations for timestamps,
 * soft deletes, and default behaviors across all feature modules.
 */
class BaseModel extends Model
{
    protected $useTimestamps = true;
    protected $useSoftDeletes = true;
    protected $protectFields = true;

    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    protected $dateFormat = 'datetime';

    /**
     * Get records with pagination info
     */
    public function getPaginated(int $perPage = 10, int $page = 1, array $where = [])
    {
        $builder = $this->builder();

        if (!empty($where)) {
            $builder->where($where);
        }

        $total = $builder->countAllResults(false);
        $data = $builder->paginate($perPage, 'default', $page);

        return [
            'data' => $data,
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total_items' => $total,
                'total_pages' => (int) ceil($total / $perPage),
            ]
        ];
    }
}
