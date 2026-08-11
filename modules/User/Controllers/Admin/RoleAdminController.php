<?php

namespace User\Controllers\Admin;

use App\Controllers\Admin\BaseAdminController;
use User\Libraries\Permission;
use User\Models\RoleModel;

class RoleAdminController extends BaseAdminController
{
    protected RoleModel $roleModel;

    public function __construct()
    {
        $this->roleModel = new RoleModel();
    }

    public function index()
    {
        if ($redirect = $this->requirePermission('roles.view')) {
            return $redirect;
        }

        $data['active'] = 'roles';
        $data['title']  = 'Roles';
        $data['items']  = $this->roleModel->findAll();

        return $this->render('admin/roles/index', $data);
    }

    public function create()
    {
        if ($redirect = $this->requirePermission('roles.create')) {
            return $redirect;
        }

        $data['active'] = 'roles';
        $data['title']  = 'New Role';
        $data['item']   = null;
        $data['permissionGroups'] = Permission::definitions();

        return $this->render('admin/roles/form', $data);
    }

    public function store()
    {
        if ($redirect = $this->requirePermission('roles.create')) {
            return $redirect;
        }

        $permissions = $this->request->getPost('permissions') ?? [];

        $data = [
            'name'        => $this->request->getPost('name'),
            'permissions' => is_array($permissions) ? array_values($permissions) : [],
        ];

        if (! $this->roleModel->insert($data)) {
            return redirect()->back()->withInput()->with('error', implode(', ', $this->roleModel->errors()));
        }

        $id = $this->roleModel->getInsertID();
        if ($id) {
            $this->logActivity('created', 'role', (int) $id, "Created role: {$data['name']}");
        }

        return redirect()->to('/admin/roles')->with('success', 'Role created successfully.');
    }

    public function edit(int $id)
    {
        if ($redirect = $this->requirePermission('roles.edit')) {
            return $redirect;
        }

        $item = $this->roleModel->find($id);

        if (! $item) {
            return redirect()->to('/admin/roles')->with('error', 'Role not found.');
        }

        $data['active'] = 'roles';
        $data['title']  = 'Edit Role';
        $data['item']   = $item;
        $data['permissionGroups'] = Permission::definitions();

        return $this->render('admin/roles/form', $data);
    }

    public function update(int $id)
    {
        if ($redirect = $this->requirePermission('roles.edit')) {
            return $redirect;
        }

        $item = $this->roleModel->find($id);

        if (! $item) {
            return redirect()->to('/admin/roles')->with('error', 'Role not found.');
        }

        if ($item->name === 'admin' && empty($this->request->getPost('permissions'))) {
            return redirect()->back()->withInput()->with('error', 'Admin role must retain permissions.');
        }

        $permissions = $this->request->getPost('permissions') ?? [];

        $data = [
            'name'        => $this->request->getPost('name'),
            'permissions' => is_array($permissions) ? array_values($permissions) : [],
        ];

        if (! $this->roleModel->update($id, $data)) {
            return redirect()->back()->withInput()->with('error', implode(', ', $this->roleModel->errors()));
        }

        $this->logActivity('updated', 'role', $id, "Updated role: {$data['name']}");

        return redirect()->to('/admin/roles')->with('success', 'Role updated successfully.');
    }

    public function delete(int $id)
    {
        if ($redirect = $this->requirePermission('roles.delete')) {
            return $redirect;
        }

        $item = $this->roleModel->find($id);

        if (! $item) {
            return redirect()->to('/admin/roles')->with('error', 'Role not found.');
        }

        if ($item->name === 'admin') {
            return redirect()->to('/admin/roles')->with('error', 'Cannot delete admin role.');
        }

        $this->roleModel->delete($id);

        $this->logActivity('deleted', 'role', $id, "Deleted role: {$item->name}");

        return redirect()->to('/admin/roles')->with('success', 'Role deleted successfully.');
    }
}
