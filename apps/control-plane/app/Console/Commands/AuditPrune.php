<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class AuditPrune extends Command
{
    protected $signature = 'audit:prune {--days= : Retention days (default: security.audit.retention_days)}';

    protected $description = 'Prune audit_logs older than retention window.';

    public function handle(): int
    {
        if (! Schema::hasTable('audit_logs')) {
            $this->warn('audit_logs table not found. Run migrations first.');
            return self::SUCCESS;
        }

        $days = (int) ($this->option('days') ?: (int) config('security.audit.retention_days', 90));
        if ($days <= 0) {
            $this->error('Retention days must be > 0.');
            return self::FAILURE;
        }

        $cutoff = Carbon::now()->subDays($days);

        $deleted = AuditLog::query()
            ->where('occurred_at', '<', $cutoff)
            ->delete();

        $this->info("Pruned {$deleted} audit log rows older than {$days} days (cutoff: {$cutoff->toDateTimeString()}).");

        return self::SUCCESS;
    }
}


