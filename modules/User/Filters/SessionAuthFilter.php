<?php
namespace User\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class SessionAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        if (!$session->has('user_id')) {
            return redirect()->to('/admin/login')->with('error', 'Please login to continue');
        }

        // Attach user info to request
        $request->user = (object) [
            'id' => $session->get('user_id'),
            'username' => $session->get('username'),
            'role_id' => $session->get('role_id'),
        ];

        return $request;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return $response;
    }
}
