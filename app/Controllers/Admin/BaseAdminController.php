<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;
use User\Libraries\Permission;

abstract class BaseAdminController extends BaseController
{
    protected array $data = [];

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        Permission::load();

        $this->data['user'] = $this->getCurrentUser();
    }

    protected function getCurrentUser(): ?array
    {
        $session = session();

        if (! $session->has('user_id')) {
            return null;
        }

        return [
            'id'       => $session->get('user_id'),
            'username' => $session->get('username'),
            'role_id'  => $session->get('role_id'),
        ];
    }

    protected function can(string $permission): bool
    {
        return Permission::has($permission);
    }

    protected function requirePermission(string $permission)
    {
        if (! $this->can($permission)) {
            return redirect()->to('/admin/dashboard')->with('error', 'You do not have permission to access this page.');
        }

        return null;
    }

    protected function logActivity(string $action, string $entityType, ?int $entityId = null, ?string $description = null): void
    {
        \User\Libraries\ActivityLog::log($action, $entityType, $entityId, $description);
    }

    protected function render(string $view, array $extraData = []): string
    {
        $data = array_merge($this->data, $extraData);

        return view('admin/layout', array_merge($data, [
            'content' => view($view, $data),
        ]));
    }
}
