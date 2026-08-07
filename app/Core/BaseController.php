<?php
namespace App\Core;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * BaseController
 *
 * Shared controller providing standardized JSON response format
 * for all API endpoints across feature modules.
 */
class BaseController extends Controller
{
    use \CodeIgniter\API\ResponseTrait;

    protected $helpers = [];

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::initController($request, $response, $logger);
    }

    /**
     * Standardized success response
     */
    protected function respond($data, int $status = 200): ResponseInterface
    {
        return $this->response->setStatusCode($status)
            ->setJSON(['success' => true, ...$data]);
    }

    /**
     * Standardized error response
     */
    protected function failResponse(string $message, int $status = 400, ?array $errors = null): ResponseInterface
    {
        $response = [
            'success' => false,
            'message' => $message
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return $this->response->setStatusCode($status)->setJSON($response);
    }

    /**
     * Validation error response
     */
    protected function failValidationErrors($errors): ResponseInterface
    {
        return $this->failResponse('Validation failed', 422, $errors);
    }

    /**
     * Not found response
     */
    protected function failNotFound(string $message = 'Resource not found'): ResponseInterface
    {
        return $this->failResponse($message, 404);
    }

    /**
     * Unauthorized response
     */
    protected function failUnauthorized(string $message = 'Unauthorized'): ResponseInterface
    {
        return $this->failResponse($message, 401);
    }

    /**
     * Forbidden response
     */
    protected function failForbidden(string $message = 'Forbidden'): ResponseInterface
    {
        return $this->failResponse($message, 403);
    }
}
