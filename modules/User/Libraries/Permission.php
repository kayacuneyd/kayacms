<?php

namespace User\Libraries;

use User\Models\RoleModel;

class Permission
{
    private static ?array $permissions = null;

    /**
     * Load permissions for current logged-in user
     */
    public static function load(): void
    {
        $session = session();

        if (! $session->has('user_id')) {
            self::$permissions = [];
            return;
        }

        $roleId = $session->get('role_id');

        if (! $roleId) {
            self::$permissions = [];
            return;
        }

        $roleModel = new RoleModel();
        $role      = $roleModel->find($roleId);

        self::$permissions = $role ? ($role->permissions ?? []) : [];
    }

    /**
     * Check if current user has a permission
     */
    public static function has(string $permission): bool
    {
        if (self::$permissions === null) {
            self::load();
        }

        $permissions = self::$permissions ?? [];

        if (in_array('*', $permissions, true)) {
            return true;
        }

        foreach ($permissions as $allowed) {
            if ($allowed === $permission) {
                return true;
            }

            $allowedPattern = rtrim($allowed, '.*');
            if (str_ends_with($allowed, '.*') && str_starts_with($permission, $allowedPattern . '.')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get all available permission definitions
     */
    public static function definitions(): array
    {
        return [
            'dashboard' => [
                'dashboard.view' => 'View Dashboard',
            ],
            'content' => [
                'content.view'   => 'View Content',
                'content.create' => 'Create Content',
                'content.edit'   => 'Edit Content',
                'content.delete' => 'Delete Content',
            ],
            'media' => [
                'media.view'   => 'View Media',
                'media.upload' => 'Upload Media',
                'media.edit'   => 'Edit Media (folders, crop/resize/rotate)',
                'media.delete' => 'Delete Media',
            ],
            'taxonomy' => [
                'taxonomy.view'   => 'View Taxonomy',
                'taxonomy.create' => 'Create Taxonomy',
                'taxonomy.edit'   => 'Edit Taxonomy',
                'taxonomy.delete' => 'Delete Taxonomy',
            ],
            'menus' => [
                'menus.view'   => 'View Menus',
                'menus.create' => 'Create Menus',
                'menus.edit'   => 'Edit Menus',
                'menus.delete' => 'Delete Menus',
            ],
            'settings' => [
                'settings.view'   => 'View Settings',
                'settings.update' => 'Update Settings',
            ],
            'themes' => [
                'themes.view'     => 'View Themes',
                'themes.activate' => 'Activate Theme',
            ],
            'users' => [
                'users.view'   => 'View Users',
                'users.create' => 'Create Users',
                'users.edit'   => 'Edit Users',
                'users.delete' => 'Delete Users',
            ],
            'roles' => [
                'roles.view'   => 'View Roles',
                'roles.create' => 'Create Roles',
                'roles.edit'   => 'Edit Roles',
                'roles.delete' => 'Delete Roles',
            ],
            'security' => [
                'security.view' => 'View Security Logs',
            ],
            'api' => [
                'api_tokens.view'   => 'View API Tokens',
                'api_tokens.create' => 'Create API Tokens',
                'api_tokens.revoke' => 'Revoke API Tokens',
                'webhooks.view'     => 'View Webhooks',
                'webhooks.manage'   => 'Manage Webhooks',
            ],
            'maintenance' => [
                'maintenance.view'   => 'View Maintenance & Backups',
                'maintenance.backup' => 'Create/Delete Backups',
                'maintenance.toggle' => 'Toggle Maintenance Mode',
            ],
            'gdpr' => [
                'gdpr.view'   => 'View GDPR Export',
                'gdpr.export' => 'Export User Data',
            ],
            'virtual_pages' => [
                'virtual_pages.view'   => 'View Virtual Pages',
                'virtual_pages.manage' => 'Manage Virtual Pages',
            ],
        ];
    }

    /**
     * Reset loaded permissions (useful for tests)
     */
    public static function reset(): void
    {
        self::$permissions = null;
    }
}
