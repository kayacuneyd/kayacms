<?php
namespace Content\Models;

use CodeIgniter\Model;

class ContentCollectionModel extends Model
{
    protected $table            = 'content_collections';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['name', 'slug', 'description'];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'name' => 'required|min_length[3]|max_length[100]',
        'slug' => 'required|alpha_dash|is_unique[content_collections.slug,id,{id}]',
    ];

    public function findBySlug(string $slug): ?array
    {
        return $this->where('slug', $slug)->first();
    }

    /**
     * Collections with item counts.
     */
    public function withCounts(): array
    {
        return $this->select('content_collections.*, COUNT(content_collection_items.id) as item_count')
            ->join('content_collection_items', 'content_collection_items.collection_id = content_collections.id', 'left')
            ->groupBy('content_collections.id')
            ->orderBy('name', 'ASC')
            ->findAll();
    }
}