<?php

namespace User\Libraries;

use User\Models\SecurityLogModel;

/**
 * Central security logging facility (severity/type/message/ip/metadata).
 */
class SecurityLog
{
    public static function log(
        string $type,
        string $message,
        string $severity = 'info',
        ?int $userId = null,
        array $metadata = []
    ): bool {
        $request = service('request');

        $ipAddress = $request instanceof \CodeIgniter\HTTP\CLIRequest
            ? 'cli'
            : $request->getIPAddress();

        $userAgent = ($request instanceof \CodeIgniter\HTTP\CLIRequest || ! method_exists($request, 'getUserAgent'))
            ? 'cli'
            : substr($request->getUserAgent()->getAgentString() ?? '', 0, 255);

        return (new SecurityLogModel())->insert([
            'severity'   => $severity,
            'type'       => $type,
            'message'    => $message,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'user_id'    => $userId,
            'metadata'   => $metadata ? json_encode($metadata) : null,
        ]) !== false;
    }

    public static function info(string $type, string $message, ?int $userId = null, array $metadata = []): bool
    {
        return self::log('info', $type, $message, $userId, $metadata);
    }

    public static function warning(string $type, string $message, ?int $userId = null, array $metadata = []): bool
    {
        return self::log($type, $message, 'warning', $userId, $metadata);
    }

    public static function critical(string $type, string $message, ?int $userId = null, array $metadata = []): bool
    {
        return self::log($type, $message, 'critical', $userId, $metadata);
    }
}