<?php
namespace Content\Models;

use App\Core\BaseModel;

class VirtualPageModel extends BaseModel
{
    protected $table            = 'virtual_pages';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'slug',
        'title',
        'handler',
        'payload',
        'status',
    ];

    protected $useTimestamps = true;

    protected $validationRules = [
        'slug'    => 'required|max_length[200]|alpha_dash|is_unique[virtual_pages.slug,id,{id}]',
        'title'   => 'required|min_length[2]|max_length[200]',
        'handler' => 'required|in_list[template,markdown,redirect]',
        'status'  => 'in_list[active,inactive]',
    ];

    protected $validationMessages = [
        'slug' => [
            'required'   => 'Slug is required',
            'alpha_dash' => 'Slug can only contain letters, numbers, dashes and underscores',
            'is_unique'  => 'Slug already exists',
        ],
        'title' => [
            'required' => 'Title is required',
        ],
    ];

    /**
     * Resolve an active virtual page by slug.
     */
    public function findBySlug(string $slug): ?array
    {
        return $this->where('slug', $slug)
            ->where('status', 'active')
            ->first();
    }

    /**
     * Decode the JSON payload into a plain array ([] when empty/broken).
     */
    public function payloadArray(?array $row = null): array
    {
        if (! $row) {
            return [];
        }

        $raw  = (string) ($row['payload'] ?? '');
        $data = json_decode($raw, true);

        return is_array($data) ? $data : [];
    }

    public function allWithStatus(): array
    {
        return $this->orderBy('id', 'DESC')->findAll();
    }
}