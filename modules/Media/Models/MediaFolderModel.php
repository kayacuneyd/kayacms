<?php
namespace Media\Models;

use App\Core\BaseModel;

class MediaFolderModel extends BaseModel
{
    protected $table            = 'media_folders';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = ['name', 'slug', 'parent_id'];

    protected $useTimestamps = true;

    public function children(?int $parentId = null)
    {
        return $this->where('parent_id', $parentId)->orderBy('name', 'ASC');
    }

    public function tree(?int $parentId = null, string $prefix = ''): array
    {
        $result = [];
        $folders = $this->where('parent_id', $parentId)->orderBy('name', 'ASC')->findAll();

        foreach ($folders as $folder) {
            $folder['label'] = $prefix . $folder['name'];
            $result[] = $folder;
            $result = array_merge($result, $this->tree($folder['id'], $prefix . '— '));
        }

        return $result;
    }

    public function findBySlug(string $slug): ?array
    {
        return $this->where('slug', $slug)->first();
    }

    public function childIds(int $folderId): array
    {
        $ids = [$folderId];

        foreach ($this->where('parent_id', $folderId)->findAll() as $child) {
            $ids = array_merge($ids, $this->childIds($child['id']));
        }

        return array_unique($ids);
    }
}