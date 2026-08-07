<?php
namespace Setting\Controllers\Api;

use App\Core\BaseController;
use Setting\Models\SettingModel;
use CodeIgniter\HTTP\ResponseInterface;

class SettingController extends BaseController
{
    protected SettingModel $model;

    public function __construct()
    {
        $this->model = new SettingModel();
    }

    public function index(): ResponseInterface
    {
        $group = $this->request->getGet('group');
        $data = $group ? $this->model->getByGroup($group) : $this->model->findAll();
        return $this->respond(['data' => $data]);
    }

    public function show($key = null): ResponseInterface
    {
        $value = $this->model->getSetting($key);
        if ($value === null) return $this->failNotFound('Setting not found');
        return $this->respond(['data' => ['key' => $key, 'value' => $value]]);
    }

    public function update($key = null): ResponseInterface
    {
        $data = $this->request->getJSON(true);
        $this->model->setSetting($key, $data['value'], $data['group'] ?? 'general', $data['type'] ?? 'string');
        return $this->respond(['message' => 'Setting updated']);
    }
}
