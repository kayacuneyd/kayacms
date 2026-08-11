<?php

namespace Content\Controllers\Admin;

use App\Controllers\Admin\BaseAdminController;
use Content\Libraries\VirtualPage;
use Content\Models\VirtualPageModel;

class VirtualPageAdminController extends BaseAdminController
{
    protected VirtualPageModel $vpModel;

    public function __construct()
    {
        $this->vpModel = new VirtualPageModel();
    }

    public function index()
    {
        if ($redirect = $this->requirePermission('virtual_pages.view')) {
            return $redirect;
        }

        $data['active'] = 'virtual_pages';
        $data['title']  = 'Virtual Pages';
        $data['items']  = $this->vpModel->allWithStatus();

        return $this->render('admin/virtual_pages/index', $data);
    }

    public function create()
    {
        if ($redirect = $this->requirePermission('virtual_pages.manage')) {
            return $redirect;
        }

        $data['active'] = 'virtual_pages';
        $data['title']  = 'New Virtual Page';
        $data['item']   = null;

        return $this->render('admin/virtual_pages/form', $data);
    }

    public function edit(int $id)
    {
        if ($redirect = $this->requirePermission('virtual_pages.manage')) {
            return $redirect;
        }

        $item = $this->vpModel->find($id);

        if (! $item) {
            return redirect()->to('/admin/virtual-pages')->with('error', 'Virtual page not found.');
        }

        $data['active'] = 'virtual_pages';
        $data['title']  = 'Edit Virtual Page';
        $data['item']   = $item;

        return $this->render('admin/virtual_pages/form', $data);
    }

    public function store()
    {
        return $this->save();
    }

    public function update(int $id)
    {
        return $this->save($id);
    }

    protected function save(?int $id = null)
    {
        if ($redirect = $this->requirePermission('virtual_pages.manage')) {
            return $redirect;
        }

        $item = $id !== null ? $this->vpModel->find($id) : null;

        if ($id !== null && ! $item) {
            return redirect()->to('/admin/virtual-pages')->with('error', 'Virtual page not found.');
        }

        $handler = (string) $this->request->getPost('handler');

        $payload = [
            'view' => trim((string) $this->request->getPost('payload_view')),
            'body' => (string) $this->request->getPost('payload_body'),
            'url'  => trim((string) $this->request->getPost('payload_url')),
        ];

        $data = [
            'slug'     => trim((string) $this->request->getPost('slug')),
            'title'    => trim((string) $this->request->getPost('title')),
            'handler'  => $handler,
            'payload'  => json_encode(array_filter($payload, static fn ($v) => $v !== '' && $v !== null), JSON_UNESCAPED_UNICODE) ?: '{}',
            'status'   => (string) $this->request->getPost('status'),
        ];

        $errors = (new VirtualPage())->validatePayload($handler, $payload);
        if ($errors) {
            return redirect()->back()->withInput()->with('error', implode(' ', $errors));
        }

        if ($id !== null) {
            if (! $this->vpModel->update($id, $data)) {
                return redirect()->back()->withInput()->with('error', implode(', ', $this->vpModel->errors()));
            }

            $this->logActivity('updated', 'virtual_page', $id, "Updated virtual page: {$data['slug']}");

            return redirect()->to('/admin/virtual-pages')->with('success', 'Virtual page updated.');
        }

        if (! $this->vpModel->insert($data)) {
            return redirect()->back()->withInput()->with('error', implode(', ', $this->vpModel->errors()));
        }

        $this->logActivity('created', 'virtual_page', (int) $this->vpModel->getInsertID(), "Created virtual page: {$data['slug']}");

        return redirect()->to('/admin/virtual-pages')->with('success', 'Virtual page created.');
    }

    public function delete(int $id)
    {
        if ($redirect = $this->requirePermission('virtual_pages.manage')) {
            return $redirect;
        }

        $item = $this->vpModel->find($id);

        if (! $item) {
            return redirect()->to('/admin/virtual-pages')->with('error', 'Virtual page not found.');
        }

        $this->vpModel->delete($id);
        $this->logActivity('deleted', 'virtual_page', $id, "Deleted virtual page: {$item['slug']}");

        return redirect()->to('/admin/virtual-pages')->with('success', 'Virtual page deleted.');
    }
}