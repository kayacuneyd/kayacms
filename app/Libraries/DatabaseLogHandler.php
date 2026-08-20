<?php

namespace App\Libraries;

use CodeIgniter\Log\Handlers\BaseHandler;
use CodeIgniter\Log\Handlers\HandlerInterface;

/**
 * Mirrors PHP-side log messages (errors, exceptions, warnings) into the
 * `error_logs` table so they're visible from the admin panel, alongside
 * the usual flat-file log.
 */
class DatabaseLogHandler extends BaseHandler implements HandlerInterface
{
    public function handle($level, $message): bool
    {
        try {
            $request = service('request');
            $url     = current_url();
            $method  = $request->getMethod();
            $agent   = (string) $request->getUserAgent();
            $ip      = $request->getIPAddress();
        } catch (\Throwable $e) {
            $url = $method = $agent = $ip = null;
        }

        try {
            $db = db_connect();

            if (! $db->tableExists('error_logs')) {
                return true;
            }

            $db->table('error_logs')->insert([
                'source'     => 'php',
                'level'      => $level,
                'message'    => (string) $message,
                'url'        => $url,
                'method'     => $method,
                'user_agent' => $agent,
                'ip_address' => $ip,
                'context'    => null,
                'resolved'   => 0,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            // Never let logging itself break the request.
        }

        return true;
    }
}
