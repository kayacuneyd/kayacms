<?php
namespace Content\Models;

use CodeIgniter\Model;

class ContentCollectionItemModel extends Model
{
    protected $table            = 'content_collection_items';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['collection_id', 'content_id', 'sort_order'];

    protected $useTimestamps = false;

    public function itemsFor(int $collectionId): array
    {
        return $this->where('collection_id', $collectionId)
            ->orderBy('sort_order', 'ASC')
            ->orderBy('id', 'ASC')
            ->findAll();
    }

    public function addItem(int $collectionId, int $contentId): bool
    {
        $exists = $this->where('collection_id', $collectionId)
            ->where('content_id', $contentId)
            ->countAllResults();

        if ($exists > 0) {
            return true;
        }

        $maxOrder = (int) $this->where('collection_id', $collectionId)->selectMax('sort_order')->first()['sort_order'] ?? 0;

        return $this->insert([
            'collection_id' => $collectionId,
            'content_id'    => $contentId,
            'sort_order'    => $maxOrder + 1,
        ]) !== false;
    }

    public function removeItem(int $collectionId, int $contentId): bool
    {
        $this->where('collection_id', $collectionId)->where('content_id', $contentId)->delete();

        return true;
    }
}