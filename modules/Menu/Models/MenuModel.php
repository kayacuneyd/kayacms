<?php
namespace Menu\Models;

use CodeIgniter\Model;

class MenuModel extends Model
{
    protected $table = 'menus';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = ['name', 'location', 'locale', 'translation_group_id'];
    protected $useTimestamps = true;

    public function withItems()
    {
        return $this->join('menu_items', 'menu_items.menu_id = menus.id', 'left')
                    ->select('menus.*, menu_items.*');
    }
}
