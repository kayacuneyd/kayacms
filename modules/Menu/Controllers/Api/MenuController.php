<?php
namespace Menu\Controllers\Api;

use App\Core\BaseController;
use Menu\Models\MenuModel;
use Menu\Models\MenuItemModel;
use CodeIgniter\HTTP\ResponseInterface;

class MenuController extends BaseController
{
    protected MenuModel $menuModel;
    protected MenuItemModel $itemModel;

    public function __construct()
    {
        $this->menuModel = new MenuModel();
        $this->itemModel = new MenuItemModel();
    }

    public function index(): ResponseInterface
    {
        $menus = $this->menuModel->findAll();
        return $this->respond(['data' => $menus]);
    }

    public function show($id = null): ResponseInterface
    {
        $menu = $this->menuModel->find($id);
        if (!$menu) return $this->failNotFound('Menu not found');

        $menu['items'] = $this->itemModel->getMenuTree($id);
        return $this->respond(['data' => $menu]);
    }

    public function create(): ResponseInterface
    {
        $data = $this->request->getJSON(true);
        if (!$this->menuModel->insert($data)) {
            return $this->failValidationErrors($this->menuModel->errors());
        }
        return $this->respondCreated(['message' => 'Menu created', 'id' => $this->menuModel->getInsertID()]);
    }

    public function update($id = null): ResponseInterface
    {
        if (!$this->menuModel->find($id)) return $this->failNotFound('Menu not found');
        $this->menuModel->update($id, $this->request->getJSON(true));
        return $this->respond(['message' => 'Menu updated']);
    }

    public function delete($id = null): ResponseInterface
    {
        if (!$this->menuModel->find($id)) return $this->failNotFound('Menu not found');
        $this->menuModel->delete($id);
        return $this->respondDeleted(['message' => 'Menu deleted']);
    }
}
