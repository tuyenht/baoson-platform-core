<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

return new class extends Migration
{
    /**
     * Migration to migrate old roles to new roles.
     * This is a one-time migration that maps:
     * - super_admin → saasOwner
     * - admin → saasAdmin (or siteAdmin if context is site-level)
     * - editor → siteEditor
     * - viewer → siteViewer
     *
     * Note: This migration is best-effort. Manual review may be needed.
     */
    public function up(): void
    {
        if (! Schema::hasTable('model_has_roles')) {
            return; // Spatie tables don't exist yet
        }

        $guard = (string) config('auth.defaults.guard', 'web');

        // Create new roles if they don't exist
        $newRoles = ['saasOwner', 'saasAdmin', 'siteOwner', 'siteAdmin', 'siteEditor', 'siteTranslator', 'siteViewer'];
        foreach ($newRoles as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => $guard]);
        }

        // Map old roles to new roles
        $mappings = [
            'super_admin' => 'saasOwner',
            'admin' => 'saasAdmin', // Default to saasAdmin, may need manual adjustment
            'editor' => 'siteEditor',
            'viewer' => 'siteViewer',
        ];

        foreach ($mappings as $oldRole => $newRole) {
            $oldRoleModel = Role::where('name', $oldRole)->where('guard_name', $guard)->first();
            $newRoleModel = Role::where('name', $newRole)->where('guard_name', $guard)->first();

            if ($oldRoleModel && $newRoleModel) {
                // Get all users with old role
                $userIds = DB::table('model_has_roles')
                    ->where('role_id', $oldRoleModel->id)
                    ->where('model_type', 'App\Models\User')
                    ->pluck('model_id');

                // Assign new role to those users (keep old role for now - manual cleanup later)
                foreach ($userIds as $userId) {
                    $user = \App\Models\User::find($userId);
                    if ($user && ! $user->hasRole($newRole)) {
                        $user->assignRole($newRole);
                    }
                }
            }
        }

        // Note: We don't delete old roles here to avoid breaking existing code.
        // Old roles can be manually removed after verification.
    }

    public function down(): void
    {
        // This migration is not reversible by design.
        // If you need to rollback, you'll need to manually reassign roles.
    }
};

