<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Support\Rbac\Rbac;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('rbac:sync {--assign-super-admin : Assign all permissions to role super_admin}', function () {
    $permissions = Rbac::syncPermissions('web');

    $this->info("Synced {$permissions->count()} permissions.");

    if ($this->option('assign-super-admin')) {
        $role = Rbac::findOrCreateRole('super_admin', 'web');
        $role->syncPermissions($permissions->pluck('name')->all());
        $this->info('Assigned all permissions to role super_admin.');
    }
})->purpose('Sync canonical RBAC permissions into DB');

// Maintenance schedules
Schedule::command('audit:prune')->daily()->description('Prune audit logs by retention window.');
Schedule::command('core:scheduler-heartbeat')->everyMinute()->description('Core scheduler heartbeat.');
