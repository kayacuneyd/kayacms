<?php
namespace Taxonomy\Controllers\Api;

use App\Core\BaseController;
use Taxonomy\Models\TermModel;
use Taxonomy\Models\TermRelationshipModel;
use CodeIgniter\HTTP\ResponseInterface;

class TermController extends BaseController
{
    protected TermModel $model;
    protected TermRelationshipModel $relationModel;

    public function __construct()
    {
        $this->model = new TermModel();
        $this->relationModel = new TermRelationshipModel();
    }

    /**
     * List terms with optional filtering
     */
    public function index(): ResponseInterface
    {
        $type = $this->request->getGet('type') ?? 'category';
        $tree = $this->request->getGet('tree') === 'true';

        if ($tree) {
            $data = $this->model->getTree($type);
        } else {
            $data = $this->model->byType($type)->findAll();
        }

        return $this->respond(['data' => $data]);
    }

    /**
     * Get single term
     */
    public function show($id = null): ResponseInterface
    {
        $term = $this->model->find($id);

        if (!$term) {
            return $this->failNotFound('Term not found');
        }

        return $this->respond(['data' => $term]);
    }

    /**
     * Create new term (requires auth)
     */
    public function create(): ResponseInterface
    {
        $data = $this->request->getJSON(true);

        if (!$this->model->insert($data)) {
            return $this->failValidationErrors($this->model->errors());
        }

        return $this->respondCreated([
            'message' => 'Term created successfully',
            'id'      => $this->model->getInsertID()
        ]);
    }

    /**
     * Update term (requires auth)
     */
    public function update($id = null): ResponseInterface
    {
        $term = $this->model->find($id);

        if (!$term) {
            return $this->failNotFound('Term not found');
        }

        $data = $this->request->getJSON(true);

        if (!$this->model->update($id, $data)) {
            return $this->failValidationErrors($this->model->errors());
        }

        return $this->respond(['message' => 'Term updated successfully']);
    }

    /**
     * Delete term (requires auth)
     */
    public function delete($id = null): ResponseInterface
    {
        $term = $this->model->find($id);

        if (!$term) {
            return $this->failNotFound('Term not found');
        }

        // Check if term has children
        $children = $this->model->getChildren($id);
        if (!empty($children)) {
            return $this->failResponse('Cannot delete term with children', 400);
        }

        $this->model->delete($id);

        return $this->respondDeleted(['message' => 'Term deleted successfully']);
    }

    /**
     * Attach terms to content (requires auth)
     */
    public function attachToContent($contentId = null): ResponseInterface
    {
        $termIds = $this->request->getJsonVar('term_ids');

        if (!is_array($termIds)) {
            return $this->failResponse('term_ids must be an array', 400);
        }

        $this->relationModel->attachTerms($contentId, $termIds);

        return $this->respond(['message' => 'Terms attached successfully']);
    }

    /**
     * Get terms for specific content
     */
    public function contentTerms($contentId = null): ResponseInterface
    {
        $terms = $this->relationModel->getContentTerms($contentId);

        return $this->respond(['data' => $terms]);
    }
}
