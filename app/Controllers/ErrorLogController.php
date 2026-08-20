<?php

namespace App\Controllers;

use App\Models\ErrorLogModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Public, unauthenticated endpoint the browser-side error-logger.js beacon
 * posts to. Kept deliberately tolerant of payload shape (sendBeacon() sends
 * a Blob, not a form) and rate-limited at the route level.
 */
class ErrorLogController extends BaseController
{
    public function collect(): ResponseInterface
    {
        if (! $this->request->is('post')) {
            return $this->response->setStatusCode(405)->setJSON(['ok' => false]);
        }

        try {
            $payload = $this->request->getJSON(true);
        } catch (\Throwable) {
            $payload = null;
        }
        if (! is_array($payload)) {
            $payload = json_decode((string) $this->request->getBody(), true);
        }
        if (! is_array($payload)) {
            $payload = $this->request->getPost();
        }

        $message = trim((string) ($payload['message'] ?? ''));
        if ($message === '') {
            return $this->response->setStatusCode(204);
        }

        $context = [
            'stack'  => substr((string) ($payload['stack'] ?? ''), 0, 4000),
            'source' => substr((string) ($payload['fileSource'] ?? ''), 0, 500),
            'line'   => (int) ($payload['line'] ?? 0),
            'column' => (int) ($payload['column'] ?? 0),
        ];

        (new ErrorLogModel())->insert([
            'source'     => 'js',
            'level'      => in_array($payload['level'] ?? '', ['error', 'unhandledrejection'], true)
                ? (string) $payload['level']
                : 'error',
            'message'    => substr($message, 0, 2000),
            'url'        => substr((string) ($payload['url'] ?? ''), 0, 500),
            'method'     => null,
            'user_agent' => substr((string) $this->request->getUserAgent(), 0, 500),
            'ip_address' => $this->request->getIPAddress(),
            'context'    => json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'resolved'   => 0,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->response->setStatusCode(204);
    }
}
