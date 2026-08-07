<?php
namespace Content\Controllers\Api;

use App\Core\BaseController;
use Content\Models\ContentModel;
use CodeIgniter\HTTP\ResponseInterface;

class ContentController extends BaseController
{
    protected ContentModel $model;

    public function __construct()
    {
        $this->model = new ContentModel();
    }

    /**
     * List content with pagination and filtering
     */
    public function index(): ResponseInterface
    {
        $type   = $this->request->getGet('type');
        $status = $this->request->getGet('status') ?? 'published';
        $search = $this->request->getGet('search');
        $limit  = (int)($this->request->getGet('limit') ?? 10);
        $page   = (int)($this->request->getGet('page') ?? 1);

        $builder = $this->model;

        if ($type) {
            $builder = $builder->byType($type);
        }

        if ($status && $status !== 'all') {
            if ($status === 'published') {
                $builder = $builder->published();
            } else {
                $builder = $builder->where('status', $status);
            }
        }

        if ($search) {
            $builder = $builder->search($search);
        }

        $contents = $builder->withAuthor()
                           ->orderBy('created_at', 'DESC')
                           ->paginate($limit, 'default', $page);

        $pager = $builder->pager;

        return $this->respond([
            'data' => $contents,
            'pagination' => [
                'current_page' => $pager->getCurrentPage(),
                'total_pages'  => $pager->getPageCount(),
                'total_items'  => $pager->getTotal(),
                'per_page'     => $limit,
            ]
        ]);
    }

    /**
     * Get single content by ID or slug
     */
    public function show($idOrSlug = null): ResponseInterface
    {
        if (is_numeric($idOrSlug)) {
            $content = $this->model->withAuthor()->find($idOrSlug);
        } else {
            $content = $this->model->withAuthor()->findBySlug($idOrSlug);
        }

        if (!$content) {
            return $this->failNotFound('Content not found');
        }

        return $this->respond(['data' => $content]);
    }

    /**
     * Create new content (requires auth)
     */
    public function create(): ResponseInterface
    {
        $data = $this->request->getJSON(true);

        // Set author from authenticated user
        if (isset($this->request->user)) {
            $data['author_id'] = $this->request->user->id;
        }

        // Auto-set published_at if status is published
        if (isset($data['status']) && $data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = date('Y-m-d H:i:s');
        }

        if (!$this->model->insert($data)) {
            return $this->failValidationErrors($this->model->errors());
        }

        return $this->respondCreated([
            'message' => 'Content created successfully',
            'id'      => $this->model->getInsertID()
        ]);
    }

    /**
     * Update existing content (requires auth)
     */
    public function update($id = null): ResponseInterface
    {
        $content = $this->model->find($id);

        if (!$content) {
            return $this->failNotFound('Content not found');
        }

        $data = $this->request->getJSON(true);

        // Auto-set published_at if status changes to published
        if (isset($data['status']) && $data['status'] === 'published' && empty($content->published_at)) {
            $data['published_at'] = date('Y-m-d H:i:s');
        }

        if (!$this->model->update($id, $data)) {
            return $this->failValidationErrors($this->model->errors());
        }

        return $this->respond(['message' => 'Content updated successfully']);
    }

    /**
     * Delete content (soft delete, requires auth)
     */
    public function delete($id = null): ResponseInterface
    {
        $content = $this->model->find($id);

        if (!$content) {
            return $this->failNotFound('Content not found');
        }

        $this->model->delete($id);

        return $this->respondDeleted(['message' => 'Content deleted successfully']);
    }
}
