<?php
namespace Content\Controllers\Api;

use App\Core\BaseController;
use App\Libraries\ContentRenderer;
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
            'data' => array_map([$this, 'decorate'], $contents),
            'pagination' => [
                'current_page' => $pager->getCurrentPage(),
                'total_pages'  => $pager->getPageCount(),
                'total_items'  => $pager->getTotal(),
                'per_page'     => $limit,
            ]
        ]);
    }

    /**
     * Search content by keyword (public endpoint)
     */
    public function search(): ResponseInterface
    {
        $search = $this->request->getGet('q');
        $type   = $this->request->getGet('type');
        $limit  = (int) ($this->request->getGet('limit') ?? 10);
        $page   = (int) ($this->request->getGet('page') ?? 1);

        $builder = $this->model;

        if ($type) {
            $builder = $builder->byType($type);
        } else {
            $builder = $builder->where('content_type', 'article');
        }

        $builder = $builder->published();

        if ($search) {
            $builder = $builder->search($search);
        }

        $contents = $builder->withAuthor()
                            ->orderBy('published_at', 'DESC')
                            ->paginate($limit, 'default', $page);

        $pager = $builder->pager;

        return $this->respond([
            'data' => $contents,
            'pagination' => [
                'current_page' => $pager->getCurrentPage(),
                'total_pages'  => $pager->getPageCount(),
                'total_items'  => $pager->getTotal(),
                'per_page'     => $limit,
            ],
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

        return $this->respond(['data' => $this->decorate($content)]);
    }

    /**
     * Create new content (requires auth)
     */
    public function create(): ResponseInterface
    {
        $data = $this->request->getJSON(true);

        if (isset($data['custom_data']) && is_array($data['custom_data'])) {
            $data['custom_data'] = json_encode($data['custom_data'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

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

        if (isset($data['custom_data']) && is_array($data['custom_data'])) {
            $data['custom_data'] = json_encode($data['custom_data'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }

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

    /**
     * Add rendered representations (html/text/excerpt) to a content row
     * without discarding the raw source column.
     */
    protected function decorate($content): array
    {
        $array = ($content instanceof \Content\Entities\ContentEntity) ? $content->toArray() : (array) $content;
        $array['render'] = [
            'html'    => ContentRenderer::render($array['body'] ?? null, $content),
            'text'    => ContentRenderer::text($array['body'] ?? null, $content),
            'excerpt' => ContentRenderer::excerpt($array['body'] ?? null, $array['excerpt'] ?? null),
        ];

        return $array;
    }
}
