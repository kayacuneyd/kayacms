<?php

namespace Maintenance\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Setting\Models\SettingModel;

/**
 * Maintenance Mode filter.
 *
 * When the `maintenance_enabled` setting is true, all requests are answered
 * with a 503 maintenance page, except:
 *   - admin routes (so admins can still log in and disable maintenance)
 *   - the maintenance check/toggle endpoints themselves
 */
class MaintenanceFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $enabled = (new SettingModel())->getSetting('maintenance_enabled', false);

        if (! $enabled) {
            return;
        }

        $path = '/'. ltrim($request->getPath(), '/');

        // Whitelist admin + auth so admin can log in and toggle maintenance mode.
        if (str_starts_with($path, '/admin')) {
            return;
        }

        return service('response')
            ->setStatusCode(503)
            ->setHeader('Retry-After', '3600')
            ->setBody(view('maintenance'));
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return $response;
    }
}