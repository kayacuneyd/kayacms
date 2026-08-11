<?php

namespace Contact\Controllers\Admin;

use App\Controllers\Admin\BaseAdminController;
use Contact\Models\ContactFormModel;

class ContactFormAdminController extends BaseAdminController
{
    protected ContactFormModel $formModel;

    public function __construct()
    {
        $this->formModel = new ContactFormModel();
    }

    public function index()
    {
        if ($redirect = $this->requirePermission('contact_forms.view')) return $redirect;

        $data['active'] = 'contact_forms';
        $data['title']  = 'Contact Forms';
        $data['items']  = $this->formModel->findAll();
        $data['canCreate'] = $this->can('contact_forms.create');
        $data['canEdit']   = $this->can('contact_forms.edit');
        $data['canDelete'] = $this->can('contact_forms.delete');

        return $this->render('admin/contact_forms/index', $data);
    }

    public function create()
    {
        if ($redirect = $this->requirePermission('contact_forms.create')) return $redirect;

        $data['active'] = 'contact_forms';
        $data['title']  = 'New Contact Form';
        $data['item']   = null;

        return $this->render('admin/contact_forms/form', $data);
    }

    public function store()
    {
        if ($redirect = $this->requirePermission('contact_forms.create')) return $redirect;

        $fields   = $this->request->getPost('fields') ?? [];
        $settings = $this->request->getPost('settings') ?? [];

        $data = [
            'name'     => $this->request->getPost('name'),
            'slug'     => url_title($this->request->getPost('slug') ?: $this->request->getPost('name'), '-', true),
            'fields'   => json_encode($this->normalizeFields($fields), JSON_UNESCAPED_UNICODE),
            'settings' => json_encode($settings, JSON_UNESCAPED_UNICODE),
            'is_active'=> $this->request->getPost('is_active') ? 1 : 0,
        ];

        if (! $this->formModel->insert($data)) {
            return redirect()->back()->withInput()->with('error', implode(', ', $this->formModel->errors()));
        }

        $id = $this->formModel->getInsertID();
        if ($id) {
            $this->logActivity('created', 'contact_form', (int) $id, "Created contact form: {$data['name']}");
        }

        return redirect()->to('/admin/contact-forms')->with('success', 'Contact form created successfully.');
    }

    public function edit(int $id)
    {
        if ($redirect = $this->requirePermission('contact_forms.edit')) return $redirect;

        $item = $this->formModel->find($id);
        if (! $item) {
            return redirect()->to('/admin/contact-forms')->with('error', 'Contact form not found.');
        }

        $data['active'] = 'contact_forms';
        $data['title']  = 'Edit Contact Form';
        $data['item']   = $item;

        return $this->render('admin/contact_forms/form', $data);
    }

    public function update(int $id)
    {
        if ($redirect = $this->requirePermission('contact_forms.edit')) return $redirect;

        $item = $this->formModel->find($id);
        if (! $item) {
            return redirect()->to('/admin/contact-forms')->with('error', 'Contact form not found.');
        }

        $fields   = $this->request->getPost('fields') ?? [];
        $settings = $this->request->getPost('settings') ?? [];

        $data = [
            'name'     => $this->request->getPost('name'),
            'slug'     => url_title($this->request->getPost('slug') ?: $this->request->getPost('name'), '-', true),
            'fields'   => json_encode($this->normalizeFields($fields), JSON_UNESCAPED_UNICODE),
            'settings' => json_encode($settings, JSON_UNESCAPED_UNICODE),
            'is_active'=> $this->request->getPost('is_active') ? 1 : 0,
        ];

        if (! $this->formModel->update($id, $data)) {
            return redirect()->back()->withInput()->with('error', implode(', ', $this->formModel->errors()));
        }

        $this->logActivity('updated', 'contact_form', $id, "Updated contact form: {$data['name']}");

        return redirect()->to('/admin/contact-forms')->with('success', 'Contact form updated successfully.');
    }

    public function delete(int $id)
    {
        if ($redirect = $this->requirePermission('contact_forms.delete')) return $redirect;

        $item = $this->formModel->find($id);
        if (! $item) {
            return redirect()->to('/admin/contact-forms')->with('error', 'Contact form not found.');
        }

        $this->formModel->delete($id);
        $this->logActivity('deleted', 'contact_form', $id, "Deleted contact form: {$item['name']}");

        return redirect()->to('/admin/contact-forms')->with('success', 'Contact form deleted successfully.');
    }

    private function normalizeFields(array $fields): array
    {
        $clean = [];
        foreach ($fields as $field) {
            if (empty($field['name'])) continue;
            $clean[] = [
                'name'     => preg_replace('/[^a-z0-9_]/', '', strtolower($field['name'])),
                'label'    => trim($field['label'] ?? $field['name']),
                'type'     => in_array($field['type'] ?? '', ['text', 'email', 'textarea', 'select', 'checkbox']) ? $field['type'] : 'text',
                'required' => ! empty($field['required']),
                'options'  => ! empty($field['options']) ? array_map('trim', explode(',', $field['options'])) : [],
            ];
        }
        return $clean;
    }
}
