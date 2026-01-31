<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== VERIFICATION REPORT ===\n\n";

// 1. Check tables
echo "1. TABLES:\n";
$tables = \Illuminate\Support\Facades\DB::select("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name");
$expectedTables = ['users', 'roles', 'permissions', 'tenants', 'sites', 'site_memberships', 'feature_flags', 'admin_menu_items', 'audit_logs'];
$foundTables = array_map(fn($t) => $t->name, $tables);
foreach ($expectedTables as $table) {
    $exists = in_array($table, $foundTables);
    echo "   " . ($exists ? "✅" : "❌") . " {$table}\n";
}
echo "   Total: " . count($tables) . " tables\n\n";

// 2. Check roles
echo "2. ROLES:\n";
$roles = \Spatie\Permission\Models\Role::pluck('name')->toArray();
$expectedRoles = ['saasOwner', 'saasAdmin', 'siteOwner', 'siteAdmin', 'siteEditor', 'siteTranslator', 'siteViewer'];
foreach ($expectedRoles as $role) {
    $exists = in_array($role, $roles);
    echo "   " . ($exists ? "✅" : "❌") . " {$role}\n";
}
echo "   Total: " . count($roles) . " roles\n\n";

// 3. Check permissions
echo "3. PERMISSIONS:\n";
$permissionCount = \Spatie\Permission\Models\Permission::count();
echo "   Total: {$permissionCount} permissions\n";
echo "   " . ($permissionCount > 0 ? "✅" : "❌") . " Permissions exist\n\n";

// 4. Check feature flags
echo "4. FEATURE FLAGS:\n";
$flags = \App\Models\FeatureFlag::pluck('key')->toArray();
$expectedFlags = ['admin.dashboard', 'admin.users', 'admin.roles', 'admin.permissions', 'admin.tenants', 'admin.settings', 'admin.audit'];
foreach ($expectedFlags as $flag) {
    $exists = in_array($flag, $flags);
    echo "   " . ($exists ? "✅" : "❌") . " {$flag}\n";
}
echo "   Total: " . count($flags) . " flags\n\n";

// 5. Check menu items
echo "5. MENU ITEMS:\n";
$menuCount = \App\Models\AdminMenuItem::count();
$rootMenus = \App\Models\AdminMenuItem::whereNull('parent_id')->pluck('label')->toArray();
echo "   Total: {$menuCount} items\n";
echo "   Root items: " . implode(', ', $rootMenus) . "\n";
echo "   " . ($menuCount > 0 ? "✅" : "❌") . " Menu items exist\n\n";

// 6. Check users
echo "6. USERS:\n";
$userCount = \App\Models\User::count();
echo "   Total: {$userCount} users\n";
if ($userCount > 0) {
    $users = \App\Models\User::with('roles')->get();
    foreach ($users as $user) {
        $roles = $user->roles->pluck('name')->toArray();
        echo "   - {$user->email} (" . implode(', ', $roles) . ")\n";
    }
}
echo "   " . ($userCount > 0 ? "✅" : "❌") . " Users exist\n\n";

echo "=== VERIFICATION COMPLETE ===\n";

