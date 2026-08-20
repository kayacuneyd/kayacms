<?php
namespace Newsletter\Models;

use CodeIgniter\Model;

class NewsletterListModel extends Model
{
    protected $table = 'newsletter_lists';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $protectFields = true;
    protected $allowedFields = ['name', 'slug', 'description', 'is_default'];
    protected $useTimestamps = true;

    public function defaultList(): ?array
    {
        return $this->where('is_default', 1)->first() ?: $this->orderBy('id', 'ASC')->first();
    }
}
