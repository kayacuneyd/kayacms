<?php
namespace Taxonomy\Models;

use CodeIgniter\Model;

class TermModel extends Model
{
    protected $table            = 'terms';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'locale',
        'translation_group_id',
        'name',
        'slug',
        'taxonomy_type',
        'parent_id',
        'description',
    ];

    protected $useTimestamps = true;

    protected $validationRules = [
        'name' => 'required|min_length[2]|max_length[200]',
        'slug' => 'required|alpha_dash|is_unique[terms.slug,id,{id}]',
        'taxonomy_type' => 'in_list[category,tag,custom]',
    ];

    /**
     * Get terms by taxonomy type
     */
    public function byType(string $type)
    {
        return $this->where('taxonomy_type', $type);
    }

    /**
     * Get root level terms (no parent)
     */
    public function rootOnly()
    {
        return $this->where('parent_id', null);
    }

    /**
     * Get children of a term
     */
    public function getChildren(int $parentId): array
    {
        return $this->where('parent_id', $parentId)->findAll();
    }

    /**
     * Filter by locale
     */
    public function locale(string $locale)
    {
        return $this->where('terms.locale', $locale);
    }

    /**
     * Get term by slug and locale
     */
    public function findBySlug(string $slug, ?string $locale = null): ?array
    {
        $builder = $this->where('slug', $slug);
        if ($locale) {
            $builder->where('locale', $locale);
        }
        return $builder->first();
    }

    /**
     * Get translations by group id
     */
    public function translations(?string $groupId): array
    {
        if (empty($groupId)) {
            return [];
        }
        return $this->where('translation_group_id', $groupId)->findAll();
    }

    /**
     * Get term hierarchy as tree filtered by locale
     */
    public function getTree(string $type = 'category', ?string $locale = null): array
    {
        $builder = $this->byType($type);
        if ($locale) {
            $builder->where('locale', $locale);
        }
        $terms = $builder->findAll();
        return $this->buildTree($terms);
    }

    /**
     * Build hierarchical tree structure
     */
    private function buildTree(array $terms, ?int $parentId = null): array
    {
        $branch = [];

        foreach ($terms as $term) {
            if ($term['parent_id'] == $parentId) {
                $children = $this->buildTree($terms, $term['id']);
                if ($children) {
                    $term['children'] = $children;
                }
                $branch[] = $term;
            }
        }

        return $branch;
    }
}
