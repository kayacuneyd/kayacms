<?php

namespace User\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use User\Libraries\RateLimiter;

class ApiRateLimitFilter implements FilterInterface
{
    protected ?array $result = null;

    public function before(RequestInterface $request, $arguments = null)
    {
        $limit  = (int) ($arguments[0] ?? RateLimiter::DEFAULT_LIMIT);
        $window = (int) ($arguments[1] ?? RateLimiter::DEFAULT_WINDOW);

        $limiter = new RateLimiter();

        // Rate limit by API token if present, otherwise by IP.
        $tokenHeader = $request->getHeaderLine('API-Key') ?: $request->getHeaderLine('X-API-Key');
        $identifier  = $tokenHeader ? 'token:' . hash('sha256', $tokenHeader) : 'ip:' . $request->getIPAddress();

        $this->result = $limiter->check($identifier, $limit, $window);

        if (! $this->result['allowed']) {
            return service('response')
                ->setStatusCode(429)
                ->setJSON([
                    'success'    => false,
                    'message'    => 'Too many requests. Rate limit exceeded.',
                    'retry_after' => max(0, $this->result['reset'] - time()),
                ]);
        }

        return $request;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        if ($this->result) {
            $response->setHeader(RateLimiter::RESPONSE_HEADER, (string) (int) ($arguments[0] ?? RateLimiter::DEFAULT_LIMIT));
            $response->setHeader(RateLimiter::REMAINING_HEADER, (string) $this->result['remaining']);
            $response->setHeader(RateLimiter::RESET_HEADER, (string) $this->result['reset']);
        }

        return $response;
    }
}