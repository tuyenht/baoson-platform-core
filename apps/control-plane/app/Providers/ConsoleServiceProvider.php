<?php

namespace App\Providers;

use App\Console\Commands\CoreDoctor;
use App\Console\Commands\CoreSchedulerHeartbeat;
use App\Console\Commands\AuditPrune;
use App\Console\Commands\RbacGrant;
use App\Console\Commands\RbacSync;
use App\Console\Commands\RbacExportPermissions;
use App\Console\Commands\I18nExport;
use Illuminate\Support\ServiceProvider;

class ConsoleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            AuditPrune::class,
            CoreDoctor::class,
            CoreSchedulerHeartbeat::class,
            I18nExport::class,
            RbacExportPermissions::class,
            RbacGrant::class,
            RbacSync::class,
        ]);
    }
}


