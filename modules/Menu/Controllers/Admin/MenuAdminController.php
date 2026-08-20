<?php

namespace Menu\Controllers\Admin;

use App\Controllers\Admin\BaseAdminController;
use Menu\Models\MenuItemModel;
use Menu\Models\MenuModel;

class MenuAdminController extends BaseAdminController
{
    protected MenuModel $menuModel;
    protected MenuItemModel $menuItemModel;

    public function __construct()
    {
        $this->menuModel = new MenuModel();
        $this->menuItemModel = new MenuItemModel();
    }

    public function index()
    {
        if ($redirect = $this->requirePermission('menus.view')) return $redirect;

        $data['active'] = 'menus';
        $data['title']  = 'Menus';
        $data['items']  = $this->menuModel->findAll();
        $data['canCreate'] = $this->can('menus.create');
        $data['canEdit']   = $this->can('menus.edit');
        $data['canDelete'] = $this->can('menus.delete');

        return $this->render('admin/menus/index', $data);
    }

    public function create()
    {
        if ($redirect = $this->requirePermission('menus.create')) return $redirect;

        $data['active'] = 'menus';
        $data['title']  = 'New Menu';
        $data['item']   = null;

        return $this->render('admin/menus/form', $data);
    }

    public function store()
    {
        if ($redirect = $this->requirePermission('menus.create')) return $redirect;

        $data = [
            'name'     => $this->request->getPost('name'),
            'location' => $this->request->getPost('location'),
            'locale'   => $this->request->getPost('locale') ?: current_locale(),
        ];

        if (! $this->menuModel->insert($data)) {
            return redirect()->back()->withInput()->with('error', implode(', ', $this->menuModel->errors()));
        }

        $id = $this->menuModel->getInsertID();
        if ($id) {
            $this->logActivity('created', 'menu', (int) $id, "Created menu: {$data['name']}");
        }

        return redirect()->to('/admin/menus')->with('success', 'Menu created successfully.');
    }

    public function edit(int $id)
    {
        if ($redirect = $this->requirePermission('menus.edit')) return $redirect;

        $item = $this->menuModel->find($id);

        if (! $item) {
            return redirect()->to('/admin/menus')->with('error', 'Menu not found.');
        }

        $data['active'] = 'menus';
        $data['title']  = 'Edit Menu';
        $data['item']   = $item;
        $data['menuItems'] = $this->menuItemModel->where('menu_id', $id)->orderBy('sort_order', 'ASC')->findAll();
        $data['contentOptions'] = (new \Content\Models\ContentModel())->where('status', 'published')->findAll();
        $data['canEditItem'] = $this->can('menus.edit');
        $data['canDeleteItem'] = $this->can('menus.delete');

        return $this->render('admin/menus/form', $data);
    }

    public function update(int $id)
    {
        if ($redirect = $this->requirePermission('menus.edit')) return $redirect;

        $item = $this->menuModel->find($id);

        if (! $item) {
            return redirect()->to('/admin/menus')->with('error', 'Menu not found.');
        }

        $data = [
            'name'     => $this->request->getPost('name'),
            'location' => $this->request->getPost('location'),
            'locale'   => $this->request->getPost('locale') ?: current_locale(),
        ];

        if (! $this->menuModel->update($id, $data)) {
            return redirect()->back()->withInput()->with('error', implode(', ', $this->menuModel->errors()));
        }

        $this->logActivity('updated', 'menu', $id, "Updated menu: {$data['name']}");

        return redirect()->to('/admin/menus')->with('success', 'Menu updated successfully.');
    }

    public function delete(int $id)
    {
        if ($redirect = $this->requirePermission('menus.delete')) return $redirect;

        $item = $this->menuModel->find($id);

        if (! $item) {
            return redirect()->to('/admin/menus')->with('error', 'Menu not found.');
        }

        $this->menuModel->delete($id);

        $this->logActivity('deleted', 'menu', $id, "Deleted menu: {$item->name}");

        return redirect()->to('/admin/menus')->with('success', 'Menu deleted successfully.');
    }

    public function storeItem(int $menuId)
    {
        if ($redirect = $this->requirePermission('menus.edit')) return $redirect;

        $menu = $this->menuModel->find($menuId);
        if (! $menu) {
            return redirect()->to('/admin/menus')->with('error', 'Menu not found.');
        }

        $data = [
            'menu_id'   => $menuId,
            'title'     => $this->request->getPost('title'),
            'url'       => $this->request->getPost('url'),
            'target'    => $this->request->getPost('target') ?: '_self',
            'parent_id' => $this->request->getPost('parent_id') ?: null,
            'sort_order'=> (int) $this->request->getPost('sort_order'),
            'locale'    => $this->request->getPost('locale') ?: ($menu['locale'] ?? current_locale()),
        ];

        if (! $this->menuItemModel->insert($data)) {
            return redirect()->back()->withInput()->with('error', implode(', ', $this->menuItemModel->errors()));
        }

        return redirect()->to('/admin/menus/edit/' . $menuId)->with('success', 'Menu item added.');
    }

    public function updateItem(int $id)
    {
        if ($redirect = $this->requirePermission('menus.edit')) return $redirect;

        $item = $this->menuItemModel->find($id);
        if (! $item) {
            return redirect()->to('/admin/menus')->with('error', 'Menu item not found.');
        }

        $data = [
            'title'     => $this->request->getPost('title'),
            'url'       => $this->request->getPost('url'),
            'target'    => $this->request->getPost('target') ?: '_self',
            'parent_id' => $this->request->getPost('parent_id') ?: null,
            'sort_order'=> (int) $this->request->getPost('sort_order'),
            'locale'    => $this->request->getPost('locale') ?: ($item['locale'] ?? current_locale()),
        ];

        $this->menuItemModel->update($id, $data);

        return redirect()->to('/admin/menus/edit/' . $item['menu_id'])->with('success', 'Menu item updated.');
    }

    public function deleteItem(int $id)
    {
        if ($redirect = $this->requirePermission('menus.delete')) return $redirect;

        $item = $this->menuItemModel->find($id);
        if (! $item) {
            return redirect()->to('/admin/menus')->with('error', 'Menu item not found.');
        }

        $menuId = $item['menu_id'];
        $this->menuItemModel->delete($id);

        return redirect()->to('/admin/menus/edit/' . $menuId)->with('success', 'Menu item deleted.');
    }

    public function reorderItems(int $menuId)
    {
        if ($redirect = $this->requirePermission('menus.edit')) return $redirect;

        $menu = $this->menuModel->find($menuId);
        if (! $menu) {
            return redirect()->to('/admin/menus')->with('error', 'Menu not found.');
        }

        $order = json_decode((string) $this->request->getPost('order'), true);
        if (! is_array($order)) {
            return redirect()->to('/admin/menus/edit/' . $menuId)->with('error', 'Invalid menu order payload.');
        }

        $this->applyItemOrder($order, $menuId, null);

        return redirect()->to('/admin/menus/edit/' . $menuId)->with('success', 'Menu order saved.');
    }

    private function applyItemOrder(array $items, int $menuId, ?int $parentId): void
    {
        foreach ($items as $index => $item) {
            $id = (int) ($item['id'] ?? 0);
            if ($id <= 0) {
                continue;
            }

            $this->menuItemModel
                ->where('menu_id', $menuId)
                ->where('id', $id)
                ->set([
                    'parent_id' => $parentId,
                    'sort_order' => ($index + 1) * 10,
                ])
                ->update();

            if (! empty($item['children']) && is_array($item['children'])) {
                $this->applyItemOrder($item['children'], $menuId, $id);
            }
        }
    }
}
