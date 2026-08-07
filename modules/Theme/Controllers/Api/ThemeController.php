<?php
namespace Theme\Controllers\Api;

use App\Core\BaseController;
use Theme\Models\ThemeModel;
use CodeIgniter\HTTP\ResponseInterface;

class ThemeController extends BaseController
{
    protected ThemeModel $model;

    public function __construct()
    {
        $this->model = new ThemeModel();
    }

    public function index(): ResponseInterface
    {
        return $this->respond(['data' => $this->model->findAll()]);
    }

    public function show($id = null): ResponseInterface
    {
        $theme = $this->model->find($id);
        if (!$theme) return $this->failNotFound('Theme not found');
        return $this->respond(['data' => $theme]);
    }

    public function activate($id = null): ResponseInterface
    {
        if (!$this->model->find($id)) return $this->failNotFound('Theme not found');
        $this->model->activate($id);
        return $this->respond(['message' => 'Theme activated']);
    }
}
