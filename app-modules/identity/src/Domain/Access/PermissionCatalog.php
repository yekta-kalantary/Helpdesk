<?php

namespace Modules\Identity\Domain\Access;

final class PermissionCatalog
{
    /** @return array<string, array<int, string>> */
    public static function groups(): array
    {
        return [
            'customers' => [
                'customers.view',
                'customers.create',
                'customers.update',
            ],
            'projects' => [
                'projects.view',
                'projects.create',
                'projects.update',
                'projects.delete',
            ],
            'tasks' => [
                'tasks.view',
                'tasks.create',
                'tasks.update',
                'tasks.delete',
                'tasks.comment',
                'tasks.manage_all',
            ],
            'tickets' => [
                'tickets.view',
                'tickets.create',
                'tickets.reply',
                'tickets.manage',
                'tickets.delete',
                'tickets.manage_all',
            ],
            'identity' => [
                'users.view',
                'users.create',
                'users.update',
                'roles.view',
                'roles.create',
                'roles.update',
                'roles.delete',
                'notifications.view',
            ],
            'reports' => [
                'reports.view',
            ],
            'settings' => [
                'settings.manage',
            ],
        ];
    }

    /** @return array<int, string> */
    public static function all(): array
    {
        return array_values(array_merge(...array_values(self::groups())));
    }
}
