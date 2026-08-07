<?php
namespace User\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use User\Libraries\JWTLib;

class ApiAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $authHeader = $request->getHeaderLine('Authorization');

        if (!str_starts_with($authHeader, 'Bearer ')) {
            return service('response')->setStatusCode(401)->setJSON([
                'success' => false,
                'message' => 'Authorization header missing or invalid'
            ]);
        }

        $token = substr($authHeader, 7);
        $jwt = new JWTLib();

        $payload = $jwt->decode($token);

        if ($payload === null) {
            return service('response')->setStatusCode(401)->setJSON([
                'success' => false,
                'message' => 'Invalid or expired token'
            ]);
        }

        // Attach user info to request
        $request->user = (object) [
            'id' => $payload['sub'],
            'username' => $payload['username'],
            'role_id' => $payload['role_id'] ?? null,
        ];

        return $request;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Add CORS headers for API
        $response->setHeader('Access-Control-Allow-Origin', '*');
        $response->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');

        return $response;
    }
}
