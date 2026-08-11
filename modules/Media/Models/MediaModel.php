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
        'file_path',
        'thumbnail_path',
        'folder_id',
        'width',
        'height',
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

    /**
     * Filter by folder (including descendants)
     */
    public function inFolder(?int $folderId, MediaFolderModel $folderModel = null)
    {
        if ($folderId === null) {
            return $this->where('folder_id', null);
        }

        $ids = ($folderModel ?? new MediaFolderModel())->childIds($folderId);

        return $this->whereIn('folder_id', $ids);
    }

    /**
     * Apply view-level path/url attachment
     */
    public function decorate(array $items): array
    {
        foreach ($items as &$item) {
            $path = $item['file_path'] ?? $item['path'] ?? '';
            $item['display_path'] = $path;
            $item['url'] = $path ? base_url($path) : '';
            $item['is_image'] = MediaHelper::isImage($item['mime_type'] ?? '');
            $item['formatted_size'] = isset($item['size'])
                ? MediaHelper::formatFileSize((int) $item['size'])
                : '';
        }

        return $items;
    }
}
