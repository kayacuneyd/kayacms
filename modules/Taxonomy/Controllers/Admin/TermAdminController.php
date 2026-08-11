<?php

namespace Taxonomy\Controllers\Admin;

use App\Controllers\Admin\BaseAdminController;
use Taxonomy\Models\TermModel;

class TermAdminController extends BaseAdminController
{
    protected TermModel $termModel;

    public function __construct()
    {
        $this->termModel = new TermModel();
    }

    public function index()
    {
        if ($redirect = $this->requirePermission('taxonomy.view')) return $redirect;

        $data['active'] = 'taxonomy';
        $data['title']  = 'Taxonomy';
        $data['items']  = $this->termModel->findAll();
        $data['canCreate'] = $this->can('taxonomy.create');
        $data['canEdit']   = $this->can('taxonomy.edit');
        $data['canDelete'] = $this->can('taxonomy.delete');

        return $this->render('admin/taxonomy/index', $data);
    }

    public function create()
    {
        if ($redirect = $this->requirePermission('taxonomy.create')) return $redirect;

        $data['active'] = 'taxonomy';
        $data['title']  = 'New Term';
        $data['item']   = null;

        return $this->render('admin/taxonomy/form', $data);
    }

    public function store()
    {
        if ($redirect = $this->requirePermission('taxonomy.create')) return $redirect;

        $data = [
            'name'          => $this->request->getPost('name'),
            'slug'          => $this->request->getPost('slug'),
            'taxonomy_type' => $this->request->getPost('taxonomy_type') ?: 'category',
            'parent_id'     => $this->request->getPost('parent_id') ?: null,
            'description'   => $this->request->getPost('description'),
        ];

        if (! $this->termModel->insert($data)) {
            return redirect()->back()->withInput()->with('error', implode(', ', $this->termModel->errors()));
        }

        $id = $this->taxonomyModel->getInsertID();
        if ($id) {
            $this->logActivity('created', 'taxonomy', (int) $id, "Created taxonomy: {$data['name']}");
        }

        return redirect()->to('/admin/taxonomys')->with('success', 'Term created successfully.');
    }

    public function edit(int $id)
    {
        if ($redirect = $this->requirePermission('taxonomy.edit')) return $redirect;

        $item = $this->termModel->find($id);

        if (! $item) {
            return redirect()->to('/admin/taxonomy')->with('error', 'Term not found.');
        }

        $data['active'] = 'taxonomy';
        $data['title']  = 'Edit Term';
        $data['item']   = $item;

        return $this->render('admin/taxonomy/form', $data);
    }

    public function update(int $id)
    {
        if ($redirect = $this->requirePermission('taxonomy.edit')) return $redirect;

        $item = $this->termModel->find($id);

        if (! $item) {
            return redirect()->to('/admin/taxonomy')->with('error', 'Term not found.');
        }

        $data = [
            'name'          => $this->request->getPost('name'),
            'slug'          => $this->request->getPost('slug'),
            'taxonomy_type' => $this->request->getPost('taxonomy_type'),
            'parent_id'     => $this->request->getPost('parent_id') ?: null,
            'description'   => $this->request->getPost('description'),
        ];

        if (! $this->termModel->update($id, $data)) {
            return redirect()->back()->withInput()->with('error', implode(', ', $this->termModel->errors()));
        }

        $this->logActivity('updated', 'taxonomy', $id, "Updated taxonomy: {$data['name']}");

        return redirect()->to('/admin/taxonomys')->with('success', 'Term updated successfully.');
    }

    public function delete(int $id)
    {
        if ($redirect = $this->requirePermission('taxonomy.delete')) return $redirect;

        $item = $this->termModel->find($id);

        if (! $item) {
            return redirect()->to('/admin/taxonomy')->with('error', 'Term not found.');
        }

        $this->termModel->delete($id);

        $this->logActivity('deleted', 'taxonomy', $id, "Deleted taxonomy: {$item->name}");

        return redirect()->to('/admin/taxonomys')->with('success', 'Term deleted successfully.');
    }
}
