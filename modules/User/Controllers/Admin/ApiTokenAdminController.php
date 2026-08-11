<?php

namespace User\Controllers\Admin;

use App\Controllers\Admin\BaseAdminController;
use User\Libraries\ApiToken;

class ApiTokenAdminController extends BaseAdminController
{
    public function index()
    {
        if ($redirect = $this->requirePermission('api_tokens.view')) {
            return $redirect;
        }

        $this->data['active'] = 'api_tokens';
        $this->data['title']  = 'API Tokens';
        $this->data['tokens'] = (new ApiToken())->forUser((int) session()->get('user_id'));

        return $this->render('admin/api_tokens/index', $this->data);
    }

    public function store()
    {
        if ($redirect = $this->requirePermission('api_tokens.create')) {
            return $redirect;
        }

        $rules = [
            'name' => 'required|min_length[3]|max_length[100]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode(', ', $this->validator->getErrors()));
        }

        $issued = (new ApiToken())->create(
            (int) session()->get('user_id'),
            (string) $this->request->getPost('name'),
            [],
            30 * 86400
        );

        return redirect()->to('/admin/api-tokens')
            ->with('success', 'Token created. Copy it now: ' . $issued['plain']);
    }

    public function revoke(int $id)
    {
        if ($redirect = $this->requirePermission('api_tokens.revoke')) {
            return $redirect;
        }

        (new ApiToken())->revoke($id);

        return redirect()->to('/admin/api-tokens')->with('success', 'Token revoked.');
    }
}