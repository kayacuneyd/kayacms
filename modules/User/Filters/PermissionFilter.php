<?php

namespace User\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use User\Libraries\Permission;

class PermissionFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if ($arguments === null || empty($arguments)) {
            return $request;
        }

        $required = is_array($arguments) ? $arguments : [$arguments];

        foreach ($required as $permission) {
            if (Permission::has($permission)) {
                return $request;
            }
        }

        // API requests get JSON, web gets redirect
        if ($request->isAJAX() || strpos($request->getUri()->getPath(), '/api/') === 0) {
            return service('response')->setStatusCode(403)
                ->setJSON(['success' => false, 'message' => 'Forbidden: missing permission']);
        }

        return redirect()->to('/admin/dashboard')->with('error', 'You do not have permission to access this page.');
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return $response;
    }
}
