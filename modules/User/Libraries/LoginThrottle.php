<?php

namespace User\Libraries;

use User\Models\LoginAttemptModel;
use User\Models\SecurityLogModel;

/**
 * Brute-force protection: tracks failed login attempts per
 * email+IP and blocks further attempts for a cooldown period.
 */
class LoginThrottle
{
    protected LoginAttemptModel $attemptModel;

    public const MAX_ATTEMPTS = 5;
    public const WINDOW       = 900; // 15 minutes
    public const LOCKOUT      = 900; // 15 minutes

    public function __construct()
    {
        $this->attemptModel = new LoginAttemptModel();
    }

    /**
     * Record a login attempt.
     */
    public function record(string $email, bool $success): void
    {
        $request = service('request');

        $this->attemptModel->insert([
            'email'      => $email,
            'ip_address' => $request->getIPAddress(),
            'user_agent' => substr($request->getUserAgent()->getAgentString() ?? '', 0, 255),
            'success'    => $success ? 1 : 0,
        ]);
    }

    /**
     * Whether the email/IP combo is currently blocked.
     */
    public function isBlocked(string $email): bool
    {
        $request = service('request');
        $ip      = $request->getIPAddress();

        $count = $this->attemptModel->where('email', $email)
            ->where('ip_address', $ip)
            ->where('success', 0)
            ->where('created_at >=', date('Y-m-d H:i:s', time() - self::WINDOW))
            ->countAllResults();

        return $count >= self::MAX_ATTEMPTS;
    }

    /**
     * Remaining lockout seconds for a blocked user.
     */
    public function remainingLockout(string $email): int
    {
        if (! $this->isBlocked($email)) {
            return 0;
        }

        $last = $this->attemptModel->where('email', $email)
            ->where('success', 0)
            ->orderBy('created_at', 'DESC')
            ->first();

        if (! $last) {
            return 0;
        }

        $expires = strtotime($last['created_at']) + self::LOCKOUT;

        return max(0, $expires - time());
    }

    /**
     * Clean up old records (housekeeping).
     */
    public function purge(int $olderThanSeconds = 86400): bool
    {
        $cutoff = date('Y-m-d H:i:s', time() - $olderThanSeconds);

        return $this->attemptModel->where('created_at <', $cutoff)->delete() >= 0;
    }
}