<?php
namespace Theme\Models;

use App\Libraries\QueryCache;
use CodeIgniter\Model;

class ThemeModel extends Model
{
    protected $table = 'themes';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['name', 'slug', 'is_active', 'config'];
    protected $useTimestamps = true;

    public function getActive()
    {
        return QueryCache::instance()->remember(
            QueryCache::key('theme_active'),
            function () {
                return $this->where('is_active', 1)->first();
            }
        );
    }

    public function activate(int $id): bool
    {
        $this->builder()->update(['is_active' => 0]); // Deactivate all
        QueryCache::instance()->forget('theme');
        return $this->update($id, ['is_active' => 1]); // Activate selected
    }
}
