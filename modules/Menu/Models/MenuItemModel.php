<?php
namespace Menu\Models;

use CodeIgniter\Model;

class MenuItemModel extends Model
{
    protected $table = 'menu_items';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['menu_id', 'title', 'url', 'content_id', 'parent_id', 'sort_order', 'target', 'locale'];
    protected $useTimestamps = true;

    public function getMenuTree(int $menuId, ?string $locale = null): array
    {
        $builder = $this->where('menu_id', $menuId);
        if ($locale) {
            $builder->where('locale', $locale);
        }
        $items = $builder->orderBy('sort_order', 'ASC')->findAll();
        return $this->buildTree($items);
    }

    private function buildTree(array $items, ?int $parentId = null): array
    {
        $branch = [];
        foreach ($items as $item) {
            if ($item['parent_id'] == $parentId) {
                $children = $this->buildTree($items, $item['id']);
                if ($children) $item['children'] = $children;
                $branch[] = $item;
            }
        }
        return $branch;
    }
}
