<?php

namespace User\Libraries;

use User\Models\NotificationModel;

/**
 * Creates and pushes notifications (in-app + optional email).
 */
class Notifications
{
    private static ?NotificationModel $model = null;

    protected static function model(): NotificationModel
    {
        if (self::$model === null) {
            self::$model = new NotificationModel();
        }

        return self::$model;
    }

    /**
     * Create a notification for a user (null = all admins / system).
     */
    public static function notify(
        ?int $recipientId,
        string $type,
        string $title,
        ?string $body = null,
        ?string $url = null
    ): bool {
        return self::model()->insert([
            'recipient_id' => $recipientId,
            'type'         => $type,
            'title'        => $title,
            'body'         => $body,
            'url'          => $url,
            'is_read'      => 0,
        ]) !== false;
    }

    /**
     * Notify the configured admin (or all users with admin role).
     */
    public static function notifyAdmins(string $type, string $title, ?string $body = null, ?string $url = null): void
    {
        $roleModel = new \User\Models\RoleModel();
        $admin = $roleModel->where('name', 'admin')->first();

        $userModel = new \User\Models\UserModel();

        $admins = $admin
            ? $userModel->where('role_id', $admin->id)->where('status', 'active')->findAll()
            : [];

        if (empty($admins)) {
            self::notify(null, $type, $title, $body, $url);
            return;
        }

        foreach ($admins as $adminUser) {
            self::notify((int) $adminUser->id, $type, $title, $body, $url);
        }
    }

    /**
     * Unread count helper.
     */
    public static function unreadCount(?int $userId): int
    {
        return self::model()->unread($userId);
    }

    /**
     * Mark all as read.
     */
    public static function markAllRead(?int $userId): bool
    {
        return self::model()->markAllRead($userId);
    }
}