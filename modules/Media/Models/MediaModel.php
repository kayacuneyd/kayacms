<?php
namespace Media\Models;

use App\Core\BaseModel;

class MediaModel extends BaseModel
{
    protected $table            = 'media';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'filename',
        'original_name',
        'mime_type',
        'size',
        'alt_text',
        'path',
        'uploaded_by',
    ];

    protected $useTimestamps = true;

    /**
     * Get images only
     */
    public function images()
    {
        return $this->like('mime_type', 'image/', 'after');
    }

    /**
     * Get documents only
     */
    public function documents()
    {
        return $this->whereIn('mime_type', ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']);
    }

    /**
     * Get media by user
     */
    public function byUser(int $userId)
    {
        return $this->where('uploaded_by', $userId);
    }

    /**
     * Search media by filename or alt text
     */
    public function search(string $keyword)
    {
        return $this->groupStart()
                    ->like('filename', $keyword)
                    ->orLike('original_name', $keyword)
                    ->orLike('alt_text', $keyword)
                    ->groupEnd();
    }
}
