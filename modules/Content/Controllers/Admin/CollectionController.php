<?php

namespace Content\Controllers\Admin;

use App\Controllers\Admin\BaseAdminController;
use Content\Models\ContentCollectionItemModel;
use Content\Models\ContentCollectionModel;
use Content\Models\ContentModel;

class CollectionController extends BaseAdminController
{
    public function index()
    {
        if ($redirect = $this->requirePermission('content.view')) {
            return $redirect;
        }

        $this->data['active'] = 'collections';
        $this->data['title']  = 'Collections';
        $this->data['collections'] = (new ContentCollectionModel())->withCounts();

        return $this->render('admin/collections/index', $this->data);
    }

    public function edit(int $id)
    {
        if ($redirect = $this->requirePermission('content.view')) {
            return $redirect;
        }

        $collection = (new ContentCollectionModel())->find($id);

        if (! $collection) {
            return redirect()->to('/admin/collections')->with('error', 'Collection not found.');
        }

        $itemIds = array_column((new ContentCollectionItemModel())->itemsFor($id), 'content_id');

        $this->data['active']     = 'collections';
        $this->data['title']      = $collection['name'];
        $this->data['collection'] = $collection;
        $this->data['items']      = $itemIds
            ? (new ContentModel())->whereIn('id', $itemIds)->orderBy('title', 'ASC')->findAll()
            : [];
        $this->data['available']  = (new ContentModel())->whereNotIn('id', $itemIds ?: [0])
            ->orderBy('title', 'ASC')->findAll(100);

        return $this->render('admin/collections/edit', $this->data);
    }

    public function store()
    {
        if ($redirect = $this->requirePermission('content.create')) {
            return $redirect;
        }

        $model = new ContentCollectionModel();

        if (! $model->insert([
            'name'        => $this->request->getPost('name'),
            'slug'        => $this->request->getPost('slug'),
            'description' => $this->request->getPost('description'),
        ])) {
            return redirect()->back()->withInput()->with('error', implode(', ', $model->errors()));
        }

        return redirect()->to('/admin/collections')->with('success', 'Collection created.');
    }

    public function update(int $id)
    {
        if ($redirect = $this->requirePermission('content.edit')) {
            return $redirect;
        }

        $model = new ContentCollectionModel();

        if (! $model->update($id, [
            'name'        => $this->request->getPost('name'),
            'slug'        => $this->request->getPost('slug'),
            'description' => $this->request->getPost('description'),
        ]) && $model->errors()) {
            return redirect()->back()->withInput()->with('error', implode(', ', $model->errors()));
        }

        return redirect()->to('/admin/collections')->with('success', 'Collection updated.');
    }

    public function delete(int $id)
    {
        if ($redirect = $this->requirePermission('content.delete')) {
            return $redirect;
        }

        (new ContentCollectionModel())->delete($id);

        return redirect()->to('/admin/collections')->with('success', 'Collection deleted.');
    }

    public function attach(int $id)
    {
        if ($redirect = $this->requirePermission('content.edit')) {
            return $redirect;
        }

        $contentId = (int) $this->request->getPost('content_id');
        (new ContentCollectionItemModel())->addItem($id, $contentId);

        return redirect()->to("/admin/collections/edit/{$id}")->with('success', 'Item added to collection.');
    }

    public function detach(int $id)
    {
        if ($redirect = $this->requirePermission('content.edit')) {
            return $redirect;
        }

        $contentId = (int) $this->request->getPost('content_id');
        (new ContentCollectionItemModel())->removeItem($id, $contentId);

        return redirect()->to("/admin/collections/edit/{$id}")->with('success', 'Item removed from collection.');
    }
}