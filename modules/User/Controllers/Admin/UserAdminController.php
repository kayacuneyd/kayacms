<?php

namespace User\Controllers\Admin;

use App\Controllers\Admin\BaseAdminController;
use User\Models\RoleModel;
use User\Models\UserModel;

class UserAdminController extends BaseAdminController
{
    protected UserModel $userModel;
    protected RoleModel $roleModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->roleModel = new RoleModel();
    }

    public function index()
    {
        if ($redirect = $this->requirePermission('users.view')) {
            return $redirect;
        }

        $data['active'] = 'users';
        $data['title']  = 'Users';
        $data['items']  = $this->userModel->withRole()->findAll();

        return $this->render('admin/users/index', $data);
    }

    public function create()
    {
        if ($redirect = $this->requirePermission('users.create')) {
            return $redirect;
        }

        $data['active'] = 'users';
        $data['title']  = 'New User';
        $data['item']   = null;
        $data['roles']  = $this->roleModel->findAll();

        return $this->render('admin/users/form', $data);
    }

    public function store()
    {
        if ($redirect = $this->requirePermission('users.create')) {
            return $redirect;
        }

        $data = [
            'username' => $this->request->getPost('username'),
            'email'    => $this->request->getPost('email'),
            'password' => $this->request->getPost('password'),
            'role_id'  => $this->request->getPost('role_id'),
            'status'   => $this->request->getPost('status') ?: 'active',
        ];

        $id = $this->userModel->insert($data);

        if (! $id) {
            return redirect()->back()->withInput()->with('error', implode(', ', $this->userModel->errors()));
        }

        $this->logActivity('created', 'user', (int) $id, "Created user: {$data['username']}");

        \App\Libraries\Hooks::doAction('user.created', (int) $id, $data['username'], $data['email']);

        return redirect()->to('/admin/users')->with('success', 'User created successfully.');
    }

    public function edit(int $id)
    {
        if ($redirect = $this->requirePermission('users.edit')) {
            return $redirect;
        }

        $item = $this->userModel->find($id);

        if (! $item) {
            return redirect()->to('/admin/users')->with('error', 'User not found.');
        }

        $data['active'] = 'users';
        $data['title']  = 'Edit User';
        $data['item']   = $item;
        $data['roles']  = $this->roleModel->findAll();

        return $this->render('admin/users/form', $data);
    }

    public function update(int $id)
    {
        if ($redirect = $this->requirePermission('users.edit')) {
            return $redirect;
        }

        $item = $this->userModel->find($id);

        if (! $item) {
            return redirect()->to('/admin/users')->with('error', 'User not found.');
        }

        $data = [
            'username' => $this->request->getPost('username'),
            'email'    => $this->request->getPost('email'),
            'role_id'  => $this->request->getPost('role_id'),
            'status'   => $this->request->getPost('status'),
        ];

        $password = $this->request->getPost('password');
        if (! empty($password)) {
            $data['password'] = $password;
        }

        if (! $this->userModel->update($id, $data)) {
            return redirect()->back()->withInput()->with('error', implode(', ', $this->userModel->errors()));
        }

        $this->logActivity('updated', 'user', $id, "Updated user: {$data['username']}");

        \App\Libraries\Hooks::doAction('user.updated', (int) $id, $data['username'], $data['email']);

        return redirect()->to('/admin/users')->with('success', 'User updated successfully.');
    }

    public function delete(int $id)
    {
        if ($redirect = $this->requirePermission('users.delete')) {
            return $redirect;
        }

        $item = $this->userModel->find($id);

        if (! $item) {
            return redirect()->to('/admin/users')->with('error', 'User not found.');
        }

        if ((int) $item->id === (int) session()->get('user_id')) {
            return redirect()->to('/admin/users')->with('error', 'Cannot delete your own account.');
        }

        $this->userModel->delete($id);

        \App\Libraries\Hooks::doAction('user.deleted', (int) $id, $item->username);

        $this->logActivity('deleted', 'user', $id, "Deleted user: {$item->username}");

        return redirect()->to('/admin/users')->with('success', 'User deleted successfully.');
    }
}
