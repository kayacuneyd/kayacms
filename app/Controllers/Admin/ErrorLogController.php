<?php

namespace App\Controllers\Admin;

use App\Models\ErrorLogModel;

class ErrorLogController extends BaseAdminController
{
    protected ErrorLogModel $model;

    public function __construct()
    {
        $this->model = new ErrorLogModel();
    }

    public function index()
    {
        $filters = [
            'source'   => (string) ($this->request->getGet('source') ?? ''),
            'level'    => (string) ($this->request->getGet('level') ?? ''),
            'resolved' => $this->request->getGet('resolved'),
            'search'   => (string) ($this->request->getGet('search') ?? ''),
        ];

        $page = (int) ($this->request->getGet('page') ?: 1);

        $result = $this->model->recent($filters, 30, $page);

        $data['active']  = 'error_logs';
        $data['title']   = 'Error Log';
        $data['items']   = $result['items'];
        $data['total']   = $result['total'];
        $data['pages']   = $result['pages'];
        $data['page']    = $page;
        $data['filters'] = $filters;
        $data['counts']  = $this->model->counts();

        return $this->render('admin/error_logs', $data);
    }

    public function resolve(int $id)
    {
        $this->model->update($id, ['resolved' => 1]);

        return redirect()->back()->with('success', 'Marked resolved.');
    }

    public function unresolve(int $id)
    {
        $this->model->update($id, ['resolved' => 0]);

        return redirect()->back()->with('success', 'Marked unresolved.');
    }

    public function delete(int $id)
    {
        $this->model->delete($id, true);

        return redirect()->back()->with('success', 'Log entry deleted.');
    }

    public function clearResolved()
    {
        $this->model->where('resolved', 1)->delete(null, true);

        return redirect()->to('/admin/error-logs')->with('success', 'Resolved entries cleared.');
    }
}
