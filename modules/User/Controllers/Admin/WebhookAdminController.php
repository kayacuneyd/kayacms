<?php

namespace User\Controllers\Admin;

use App\Controllers\Admin\BaseAdminController;
use User\Libraries\Webhook;

class WebhookAdminController extends BaseAdminController
{
    public function index()
    {
        if ($redirect = $this->requirePermission('webhooks.manage')) {
            return $redirect;
        }

        $this->data['active'] = 'webhooks';
        $this->data['title']  = 'Webhooks';
        $this->data['webhooks'] = \Config\Database::connect()
            ->table('webhooks')
            ->orderBy('created_at', 'DESC')
            ->get()
            ->getResultArray();

        return $this->render('admin/webhooks/index', $this->data);
    }

    public function store()
    {
        if ($redirect = $this->requirePermission('webhooks.manage')) {
            return $redirect;
        }

        $rules = [
            'name'  => 'required|min_length[3]|max_length[100]',
            'url'   => 'required|valid_url|max_length[500]',
            'event' => 'required|in_list[content.published,content.updated,content.deleted,comment.created,contact.submitted,user.created]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode(', ', $this->validator->getErrors()));
        }

        $secret = bin2hex(random_bytes(16));

        \Config\Database::connect()->table('webhooks')->insert([
            'name'   => $this->request->getPost('name'),
            'url'    => $this->request->getPost('url'),
            'event'  => $this->request->getPost('event'),
            'secret' => $secret,
        ]);

        return redirect()->to('/admin/webhooks')->with('success', 'Webhook created. Secret: ' . $secret);
    }

    public function toggle(int $id)
    {
        if ($redirect = $this->requirePermission('webhooks.manage')) {
            return $redirect;
        }

        $row = \Config\Database::connect()->table('webhooks')->where('id', $id)->get()->getRowArray();

        if (! $row) {
            return redirect()->to('/admin/webhooks')->with('error', 'Webhook not found.');
        }

        \Config\Database::connect()->table('webhooks')->where('id', $id)
            ->update(['is_active' => $row['is_active'] ? 0 : 1]);

        return redirect()->to('/admin/webhooks')->with('success', 'Webhook status updated.');
    }

    public function delete(int $id)
    {
        if ($redirect = $this->requirePermission('webhooks.manage')) {
            return $redirect;
        }

        \Config\Database::connect()->table('webhooks')->where('id', $id)->delete();

        return redirect()->to('/admin/webhooks')->with('success', 'Webhook deleted.');
    }

    public function deliveries()
    {
        if ($redirect = $this->requirePermission('webhooks.manage')) {
            return $redirect;
        }

        $this->data['active'] = 'webhooks';
        $this->data['title']  = 'Webhook Deliveries';
        $this->data['deliveries'] = (new Webhook())->recentDeliveries(50);

        return $this->render('admin/webhooks/deliveries', $this->data);
    }
}