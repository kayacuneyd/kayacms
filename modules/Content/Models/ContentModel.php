<?php
namespace Content\Models;

use App\Core\BaseModel;
use Content\Entities\ContentEntity;

class ContentModel extends BaseModel
{
    protected $table            = 'content';
    protected $primaryKey       = 'id';
    protected $returnType       = ContentEntity::class;
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'locale',
        'translation_group_id',
        'content_type',
        'title',
        'slug',
        'body',
        'excerpt',
        'status',
        'author_id',
        'featured_image',
        'meta_title',
        'meta_description',
        'published_at',
        'is_featured',
        'custom_data',
    ];

    protected $useTimestamps = true;

    protected $validationRules = [
        'title' => 'required|min_length[3]|max_length[255]',
        'slug'  => 'required|alpha_dash|is_unique[content.slug,id,{id}]',
        'body'  => 'required',
        'status' => 'in_list[draft,published,archived]',
        'content_type' => 'in_list[article,page,product]',
    ];

    protected $validationMessages = [
        'title' => [
            'required' => 'Title is required',
            'min_length' => 'Title must be at least 3 characters',
        ],
        'slug' => [
            'required' => 'Slug is required',
            'is_unique' => 'Slug already exists',
            'alpha_dash' => 'Slug can only contain letters, numbers, dashes and underscores',
        ],
    ];

    /**
     * Get published content only
     */
    public function published()
    {
        return $this->where('content.status', 'published')
                    ->where('content.published_at <=', date('Y-m-d H:i:s'));
    }

    /**
     * Filter by content type
     */
    public function byType(string $type)
    {
        return $this->where('content_type', $type);
    }

    /**
     * Get content with author information
     */
    public function withAuthor()
    {
        return $this->join('users', 'users.id = content.author_id', 'left')
                    ->select('content.*, users.username as author_name, users.email as author_email');
    }

    /**
     * Search content by keyword
     */
    public function search(string $keyword)
    {
        return $this->groupStart()
                    ->like('title', $keyword)
                    ->orLike('body', $keyword)
                    ->orLike('excerpt', $keyword)
                    ->groupEnd();
    }

    public function locale(string $locale)
    {
        return $this->where('content.locale', $locale);
    }

    /**
     * Get translations of a content by group id
     */
    public function translations(?string $groupId): array
    {
        if (empty($groupId)) {
            return [];
        }
        return $this->where('translation_group_id', $groupId)
                    ->findAll();
    }

    /**
     * Get content by slug and locale
     */
    public function findBySlug(string $slug, ?string $locale = null): ?ContentEntity
    {
        $builder = $this->where('slug', $slug);
        if ($locale) {
            $builder->where('locale', $locale);
        }
        return $builder->first();
    }
}
