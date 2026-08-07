<?php
namespace Media\Controllers\Api;

use App\Core\BaseController;
use Media\Models\MediaModel;
use Media\Helpers\MediaHelper;
use CodeIgniter\HTTP\ResponseInterface;

class MediaController extends BaseController
{
    protected MediaModel $model;

    public function __construct()
    {
        $this->model = new MediaModel();
    }

    /**
     * List media with pagination
     */
    public function index(): ResponseInterface
    {
        $type   = $this->request->getGet('type'); // image, document
        $search = $this->request->getGet('search');
        $limit  = (int)($this->request->getGet('limit') ?? 20);
        $page   = (int)($this->request->getGet('page') ?? 1);

        $builder = $this->model;

        if ($type === 'image') {
            $builder = $builder->images();
        } elseif ($type === 'document') {
            $builder = $builder->documents();
        }

        if ($search) {
            $builder = $builder->search($search);
        }

        $media = $builder->orderBy('created_at', 'DESC')
                        ->paginate($limit, 'default', $page);

        $pager = $builder->pager;

        // Add formatted file sizes and URLs
        foreach ($media as &$item) {
            $item['formatted_size'] = MediaHelper::formatFileSize($item['size']);
            $item['url'] = base_url($item['path']);
            $item['is_image'] = MediaHelper::isImage($item['mime_type']);
        }

        return $this->respond([
            'data' => $media,
            'pagination' => [
                'current_page' => $pager->getCurrentPage(),
                'total_pages'  => $pager->getPageCount(),
                'total_items'  => $pager->getTotal(),
                'per_page'     => $limit,
            ]
        ]);
    }

    /**
     * Get single media item
     */
    public function show($id = null): ResponseInterface
    {
        $media = $this->model->find($id);

        if (!$media) {
            return $this->failNotFound('Media not found');
        }

        $media['formatted_size'] = MediaHelper::formatFileSize($media['size']);
        $media['url'] = base_url($media['path']);
        $media['is_image'] = MediaHelper::isImage($media['mime_type']);

        return $this->respond(['data' => $media]);
    }

    /**
     * Upload new media (requires auth)
     */
    public function upload(): ResponseInterface
    {
        $file = $this->request->getFile('file');

        if (!$file) {
            return $this->failResponse('No file uploaded', 400);
        }

        // Validate file
        $errors = MediaHelper::validateFile($file);
        if (!empty($errors)) {
            return $this->failValidationErrors($errors);
        }

        // Generate filename and path
        $originalName = $file->getClientName();
        $filename = MediaHelper::generateFilename($originalName);
        $uploadPath = MediaHelper::getUploadPath();

        // Move file
        if (!$file->move($uploadPath, $filename)) {
            return $this->failResponse('Failed to upload file', 500);
        }

        // Save to database
        $data = [
            'filename'      => $filename,
            'original_name' => $originalName,
            'mime_type'     => $file->getMimeType(),
            'size'          => $file->getSize(),
            'path'          => MediaHelper::getRelativePath($uploadPath . '/' . $filename),
            'alt_text'      => $this->request->getPost('alt_text'),
            'uploaded_by'   => $this->request->user->id ?? null,
        ];

        if (!$this->model->insert($data)) {
            return $this->failResponse('Failed to save media record', 500, $this->model->errors());
        }

        $mediaId = $this->model->getInsertID();
        $media = $this->model->find($mediaId);
        $media['url'] = base_url($media['path']);

        return $this->respondCreated([
            'message' => 'File uploaded successfully',
            'data'    => $media
        ]);
    }

    /**
     * Update media metadata (requires auth)
     */
    public function update($id = null): ResponseInterface
    {
        $media = $this->model->find($id);

        if (!$media) {
            return $this->failNotFound('Media not found');
        }

        $data = [
            'alt_text' => $this->request->getJsonVar('alt_text'),
        ];

        if (!$this->model->update($id, $data)) {
            return $this->failValidationErrors($this->model->errors());
        }

        return $this->respond(['message' => 'Media updated successfully']);
    }

    /**
     * Delete media (requires auth)
     */
    public function delete($id = null): ResponseInterface
    {
        $media = $this->model->find($id);

        if (!$media) {
            return $this->failNotFound('Media not found');
        }

        // Delete physical file
        $filePath = FCPATH . $media['path'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // Soft delete from database
        $this->model->delete($id);

        return $this->respondDeleted(['message' => 'Media deleted successfully']);
    }
}
