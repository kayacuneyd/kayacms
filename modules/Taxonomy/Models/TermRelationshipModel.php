<?php
namespace Taxonomy\Models;

use CodeIgniter\Model;

class TermRelationshipModel extends Model
{
    protected $table            = 'term_relationships';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = ['content_id', 'term_id'];

    protected $useTimestamps = false;
    protected $createdField  = 'created_at';

    /**
     * Attach terms to content
     */
    public function attachTerms(int $contentId, array $termIds): bool
    {
        // Remove existing relationships
        $this->where('content_id', $contentId)->delete();

        // Add new relationships
        $data = [];
        foreach ($termIds as $termId) {
            $data[] = [
                'content_id' => $contentId,
                'term_id' => $termId,
                'created_at' => date('Y-m-d H:i:s'),
            ];
        }

        return $this->insertBatch($data);
    }

    /**
     * Get terms for content
     */
    public function getContentTerms(int $contentId): array
    {
        return $this->select('terms.*')
                    ->join('terms', 'terms.id = term_relationships.term_id')
                    ->where('content_id', $contentId)
                    ->findAll();
    }

    /**
     * Get content by term
     */
    public function getTermContent(int $termId): array
    {
        return $this->select('content.*')
                    ->join('content', 'content.id = term_relationships.content_id')
                    ->where('term_id', $termId)
                    ->findAll();
    }
}
