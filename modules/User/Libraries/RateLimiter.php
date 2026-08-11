<?php

namespace User\Libraries;

use CodeIgniter\HTTP\RequestInterface;

/**
 * Simple fixed-window rate limiter backed by the database.
 * Supports both per-IP and per-token identifiers.
 */
class RateLimiter
{
    public const DEFAULT_LIMIT     = 60;
    public const DEFAULT_WINDOW    = 60; // seconds
    public const RESPONSE_HEADER   = 'X-RateLimit-Limit';
    public const REMAINING_HEADER  = 'X-RateLimit-Remaining';
    public const RESET_HEADER      = 'X-RateLimit-Reset';

    protected $table = 'api_rate_limits';

    /**
     * Check a request against the quota.
     *
     * @return array{allowed: bool, remaining: int, reset: int}
     */
    public function check(string $identifier, int $limit = self::DEFAULT_LIMIT, int $window = self::DEFAULT_WINDOW): array
    {
        $db = \Config\Database::connect();
        $now = date('Y-m-d H:i:s');
        $windowStart = date('Y-m-d H:i:s', time() - $window);

        $db->transBegin();

        $row = $db->table($this->table)
            ->where('identifier', $identifier)
            ->get()
            ->getRowArray();

        if (! $row) {
            $db->table($this->table)->insert([
                'identifier'        => $identifier,
                'hit_count'         => 1,
                'window_started_at' => $now,
                'last_request_at'   => $now,
            ]);

            $db->transCommit();

            return [
                'allowed'   => true,
                'remaining' => $limit - 1,
                'reset'     => time() + $window,
            ];
        }

        // Window expired => reset counter
        if ($row['window_started_at'] < $windowStart) {
            $db->table($this->table)
                ->where('id', $row['id'])
                ->update([
                    'hit_count'         => 1,
                    'window_started_at' => $now,
                    'last_request_at'   => $now,
                ]);

            $db->transCommit();

            return [
                'allowed'   => true,
                'remaining' => $limit - 1,
                'reset'     => strtotime($row['window_started_at']) + $window,
            ];
        }

        $hits   = (int) $row['hit_count'] + 1;
        $allowed = $hits <= $limit;

        if ($allowed) {
            $db->table($this->table)
                ->where('id', $row['id'])
                ->update([
                    'hit_count'       => $hits,
                    'last_request_at' => $now,
                ]);
        }

        $db->transCommit();

        $remaining = max(0, $limit - $hits);

        return [
            'allowed'   => $allowed,
            'remaining' => $allowed ? $remaining : 0,
            'reset'     => strtotime($row['window_started_at']) + $window,
        ];
    }
}