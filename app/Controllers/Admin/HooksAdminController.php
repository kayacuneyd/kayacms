<?php

namespace App\Controllers\Admin;

use App\Libraries\Hooks;

/**
 * Lists the canonical (documented) event names fired by the CMS plus any
 * hooks actually registered during the current request. Read-only reference
 * page for module/plugin authors.
 */
class HooksAdminController extends BaseAdminController
{
    public function index()
    {
        if ($redirect = $this->requirePermission('settings.update')) return $redirect;

        $data['active'] = 'hooks';
        $data['title']  = 'Hooks & Events';

        $data['events'] = [
            'content.created' => ['type' => 'action', 'description' => 'New content record inserted. Args: id, title, slug, status.'],
            'content.updated' => ['type' => 'action', 'description' => 'Existing content record updated. Args: id, title, slug, status.'],
            'content.deleted' => ['type' => 'action', 'description' => 'Content record deleted. Args: id, title.'],
            'comment.created' => ['type' => 'action', 'description' => 'New comment submitted. Args: id, contentId, payload[].'],
            'user.created'    => ['type' => 'action', 'description' => 'Admin user created. Args: id, username, email.'],
            'user.updated'    => ['type' => 'action', 'description' => 'Admin user updated. Args: id, username, email.'],
            'user.deleted'    => ['type' => 'action', 'description' => 'Admin user deleted. Args: id, username.'],
        ];

        $data['registered'] = Hooks::registered();

        return $this->render('admin/hooks/index', $data);
    }
}