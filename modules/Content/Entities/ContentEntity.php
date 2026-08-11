<?php
namespace Content\Entities;

use CodeIgniter\Entity\Entity;

class ContentEntity extends Entity
{
    protected $attributes = [
        'id'                   => null,
        'locale'               => 'tr',
        'translation_group_id' => null,
        'content_type'         => 'article',
        'title'                => null,
        'slug'                 => null,
        'body'                 => null,
        'excerpt'              => null,
        'custom_data'          => null,
        'status'               => 'draft',
        'author_id'            => null,
        'featured_image'       => null,
        'meta_title'           => null,
        'meta_description'     => null,
        'published_at'         => null,
        'created_at'           => null,
        'updated_at'           => null,
        'deleted_at'           => null,
    ];

    protected $casts = [
        'id'          => 'integer',
        'author_id'   => 'integer',
        'custom_data' => '?json-array',
    ];

    protected $dates = [
        'published_at',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * Auto-generate slug from title if not provided
     */
    public function setTitle(string $title)
    {
        $this->attributes['title'] = $title;

        // Auto-generate slug if not set
        if (empty($this->attributes['slug'])) {
            $this->attributes['slug'] = $this->generateSlug($title);
        }

        return $this;
    }

    /**
     * Generate URL-friendly slug
     */
    private function generateSlug(string $text): string
    {
        $text = strtolower($text);
        $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
        $text = preg_replace('/[\s-]+/', '-', $text);
        $text = trim($text, '-');

        return $text;
    }

    /**
     * Check if content is published
     */
    public function isPublished(): bool
    {
        return $this->attributes['status'] === 'published'
            && !empty($this->attributes['published_at'])
            && strtotime($this->attributes['published_at']) <= time();
    }
}
