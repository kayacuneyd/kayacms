<?php

namespace User\Libraries;

use User\Models\ActivityLogModel;

class ActivityLog
{
    private static ?ActivityLogModel $model = null;

    protected static function getModel(): ActivityLogModel
    {
        if (self::$model === null) {
            self::$model = new ActivityLogModel();
        }

        return self::$model;
    }

    /**
     * Log an admin activity
     */
    public static function log(
        string $action,
        string $entityType,
        ?int $entityId = null,
        ?string $description = null
    ): bool {
        $request = service('request');
        $session = session();

        $data = [
            'user_id'     => $session->get('user_id') ?: null,
            'username'    => $session->get('username') ?: 'system',
            'action'      => $action,
            'entity_type' => $entityType,
            'entity_id'   => $entityId,
            'description' => $description,
            'ip_address'  => $request->getIPAddress(),
            'user_agent'  => substr($request->getUserAgent()->getAgentString() ?? '', 0, 255),
            'created_at'  => date('Y-m-d H:i:s'),
        ];

        return self::getModel()->insert($data) !== false;
    }

    /**
     * Shortcut for created actions
     */
    public static function created(string $entityType, int $entityId, string $description = ''): bool
    {
        return self::log('created', $entityType, $entityId, $description);
    }

    /**
     * Shortcut for updated actions
     */
    public static function updated(string $entityType, int $entityId, string $description = ''): bool
    {
        return self::log('updated', $entityType, $entityId, $description);
    }

    /**
     * Shortcut for deleted actions
     */
    public static function deleted(string $entityType, int $entityId, string $description = ''): bool
    {
        return self::log('deleted', $entityType, $entityId, $description);
    }

    /**
     * Shortcut for login/logout actions
     */
    public static function auth(string $action, ?int $userId = null, ?string $username = null, string $description = ''): bool
    {
        $request = service('request');

        $data = [
            'user_id'     => $userId,
            'username'    => $username ?? 'system',
            'action'      => $action,
            'entity_type' => 'auth',
            'entity_id'   => $userId,
            'description' => $description,
            'ip_address'  => $request->getIPAddress(),
            'user_agent'  => substr($request->getUserAgent()->getAgentString() ?? '', 0, 255),
            'created_at'  => date('Y-m-d H:i:s'),
        ];

        return self::getModel()->insert($data) !== false;
    }
}
