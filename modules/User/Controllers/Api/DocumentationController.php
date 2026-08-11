<?php

namespace User\Controllers\Api;

use App\Core\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class DocumentationController extends BaseController
{
    /**
     * Serve the OpenAPI 3.0 JSON spec describing the public API.
     */
    public function openapi(): ResponseInterface
    {
        $spec = [
            'openapi' => '3.0.3',
            'info'    => [
                'title'       => 'KayaCMS API',
                'description' => 'REST API for KayaCMS. Authenticate via `/api/auth/login` (Bearer JWT) or via personal access tokens (`API-Key` header).',
                'version'     => '1.0.0',
            ],
            'tags' => [
                ['name' => 'auth'],
                ['name' => 'tokens'],
            ],
            'paths' => [
                '/api/auth/login' => $this->jsonMethod('auth', 'Authenticate and receive a JWT.', [
                    'email' => ['type' => 'string', 'format' => 'email'],
                    'password' => ['type' => 'string', 'format' => 'password'],
                ], '200'),
                '/api/auth/register' => $this->jsonMethod('auth', 'Create a new user account.', [
                    'username' => ['type' => 'string'],
                    'email' => ['type' => 'string', 'format' => 'email'],
                    'password' => ['type' => 'string', 'format' => 'password'],
                ], '201'),
                '/api/auth/me' => $this->getMethod('auth', 'Return the authenticated user profile.', true),
                '/api/auth/refresh' => $this->jsonMethod('auth', 'Refresh the JWT.', [], '200', true),
                '/api/tokens' => [
                    'get'  => $this->getMethod('tokens', 'List personal access tokens.', true),
                    'post' => $this->jsonMethod('tokens', 'Issue a personal access token.', [
                        'name' => ['type' => 'string'],
                        'scopes' => ['type' => 'array', 'items' => ['type' => 'string']],
                    ], '201', true),
                ],
                '/api/tokens/{id}' => [
                    'delete' => $this->getMethod('tokens', 'Revoke a personal access token.', true),
                ],
            ],
        ];

        return $this->response
            ->setContentType('application/json')
            ->setBody(json_encode($spec));
    }

    /**
     * Server-rendered HTML documentation page.
     */
    public function docs()
    {
        return view('admin/api_docs');
    }

    protected function jsonMethod(string $tag, string $summary, array $properties, string $successCode, bool $secured = false): array
    {
        $method = [
            'tags'    => [$tag],
            'summary' => $summary,
            'responses' => $this->responses($successCode),
        ];

        if ($secured) {
            $method['security'] = [['BearerAuth' => []]];
        }

        if ($properties) {
            $method['requestBody'] = [
                'required' => true,
                'content'  => [
                    'application/json' => [
                        'schema' => ['type' => 'object', 'properties' => $properties],
                    ],
                ],
            ];
        }

        return $method;
    }

    protected function getMethod(string $tag, string $summary, bool $secured = false): array
    {
        $method = [
            'tags'      => [$tag],
            'summary'   => $summary,
            'responses' => $this->responses('200'),
        ];

        if ($secured) {
            $method['security'] = [['BearerAuth' => []]];
        }

        return $method;
    }

    protected function responses(string $code = '200'): array
    {
        return [
            $code       => ['description' => 'Successful response'],
            '400'       => ['description' => 'Bad request'],
            '401'       => ['description' => 'Unauthorized'],
            '422'       => ['description' => 'Validation failed'],
            '429'       => ['description' => 'Rate limit exceeded'],
        ];
    }
}