<?php

namespace User\Controllers\Admin;

use App\Controllers\Admin\BaseAdminController;
use User\Models\NotificationModel;

class NotificationAdminController extends BaseAdminController
{
    protected NotificationModel $model;

    public function __construct()
    {
        $this->model = new NotificationModel();
    }

    public function index()
    {
        if ($redirect = $this->requirePermission('dashboard.view')) return $redirect;

        $userId = session()->get('user_id');
        $perPage = (int) ($this->request->getGet('per_page') ?: 20);
        $page    = (int) ($this->request->getGet('page') ?: 1);

        $this->model->forUser($userId)->orderBy('created_at', 'DESC');
        $total = (int) $this->model->countAllResults(false);
        $items = $this->model->findAll($perPage, ($page - 1) * $perPage);

        $data['active'] = 'notifications';
        $data['title']  = 'Notifications';
        $data['items']  = $items;
        $data['pagination'] = [
            'current_page' => $page,
            'per_page'     => $perPage,
            'total_items'  => $total,
            'total_pages'  => (int) ceil($total / $perPage),
        ];

        $this->model->markAllRead($userId);

        return $this->render('admin/notifications/index', $data);
    }

    public function markRead(int $id)
    {
        $userId = session()->get('user_id');
        $this->model->where('id', $id);
        if ($userId) {
            $this->model->where('recipient_id', $userId);
        }
        $this->model->set(['is_read' => 1])->update();

        return redirect()->to('/admin/notifications');
    }

    public function clear()
    {
        $userId = session()->get('user_id');
        $this->model->forUser($userId)->where('is_read', 1)->delete();

        return redirect()->to('/admin/notifications')->with('success', 'Notifications cleared.');
    }
}