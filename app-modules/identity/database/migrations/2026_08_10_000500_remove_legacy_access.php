<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('roles') || ! Schema::hasTable('permissions')) {
            return;
        }

        $legacyPermissions = DB::table('permissions')
            ->where('guard_name', 'web')
            ->where(function ($query): void {
                $query->where('name', 'like', 'customers.%')
                    ->orWhere('name', 'like', 'tickets.%')
                    ->orWhere('name', 'like', 'reports.%')
                    ->orWhere('name', 'like', 'settings.%')
                    ->orWhere('name', 'notifications.view');
            })
            ->pluck('id');

        if ($legacyPermissions->isNotEmpty()) {
            if (Schema::hasTable('role_has_permissions')) {
                DB::table('role_has_permissions')->whereIn('permission_id', $legacyPermissions)->delete();
            }
            if (Schema::hasTable('model_has_permissions')) {
                DB::table('model_has_permissions')->whereIn('permission_id', $legacyPermissions)->delete();
            }
            DB::table('permissions')->whereIn('id', $legacyPermissions)->delete();
        }

        $customerRole = DB::table('roles')->where('guard_name', 'web')->where('name', 'customer')->first(['id']);
        if (! $customerRole) {
            return;
        }

        if (Schema::hasTable('role_has_permissions')) {
            DB::table('role_has_permissions')->where('role_id', $customerRole->id)->delete();
        }
        if (Schema::hasTable('model_has_roles')) {
            DB::table('model_has_roles')->where('role_id', $customerRole->id)->delete();
        }
        DB::table('roles')->where('id', $customerRole->id)->delete();
    }

    public function down(): void
    {
        // Removed permissions are defined by deleted modules and are intentionally not recreated.
    }
};
