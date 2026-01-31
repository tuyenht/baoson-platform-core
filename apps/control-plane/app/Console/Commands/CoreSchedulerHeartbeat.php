<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class CoreSchedulerHeartbeat extends Command
{
    protected $signature = 'core:scheduler-heartbeat';

    protected $description = 'Write a heartbeat so core:doctor can verify the scheduler is running.';

    public function handle(): int
    {
        $key = 'core:scheduler:last_run';
        $ttl = (int) config('core.scheduler.heartbeat_ttl_seconds', 3600);

        Cache::put($key, now()->toIso8601String(), $ttl);

        $this->info("Scheduler heartbeat written: {$key}");

        return self::SUCCESS;
    }
}


