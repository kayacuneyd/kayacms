<?php
namespace Content\Models;

use CodeIgniter\Model;

class ContentRelationModel extends Model
{
    protected $table            = 'content_relations';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['source_id', 'target_id', 'type'];

    protected $useTimestamps = false;

    /**
     * Add a relation between two content items.
     */
    public function addRelation(int $sourceId, int $targetId, string $type = 'related'): void
    {
        if ($sourceId === $targetId) {
            return;
        }

        $exists = $this->where('source_id', $sourceId)
            ->where('target_id', $targetId)
            ->where('type', $type)
            ->countAllResults();

        if ($exists === 0) {
            $this->insert([
                'source_id' => $sourceId,
                'target_id' => $targetId,
                'type'      => $type,
            ]);
        }
    }

    /**
     * Remove a relation.
     */
    public function removeRelation(int $sourceId, int $targetId, ?string $type = null): void
    {
        $builder = $this->where('source_id', $sourceId)->where('target_id', $targetId);

        if ($type) {
            $builder->where('type', $type);
        }

        $builder->delete();
    }

    /**
     * Remove all outgoing relations for a source content item.
     */
    public function clearRelations(int $contentId): void
    {
        $this->where('source_id', $contentId)->delete();
    }

    /**
     * Related content ids for a source (both directions for symmetric types).
     */
    public function relatedIds(int $contentId, string $type = 'related'): array
    {
        $rows = $this->where('source_id', $contentId)
            ->where('type', $type)
            ->findAll();

        $ids = array_column($rows, 'target_id');

        if ($type === 'related') {
            $inverse = $this->where('target_id', $contentId)
                ->where('type', $type)
                ->findAll();
            $ids = array_merge($ids, array_column($inverse, 'source_id'));
        }

        return array_values(array_unique(array_map('intval', $ids)));
    }
}