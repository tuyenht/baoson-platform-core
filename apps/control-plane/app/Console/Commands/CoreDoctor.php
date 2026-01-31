<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Schema;

class CoreDoctor extends Command
{
    protected $signature = 'core:doctor {--json : Output as JSON}';

    protected $description = 'Check environment, storage drivers, and core platform prerequisites (RBAC/2FA/OAuth).';

    public function handle(): int
    {
        $checks = [];

        $add = function (string $name, bool $ok, string $message, array $meta = []) use (&$checks): void {
            $checks[] = [
                'name' => $name,
                'ok' => $ok,
                'message' => $message,
                'meta' => $meta,
            ];
        };

        // APP_KEY
        $appKey = (string) config('app.key', '');
        $add('app.key', $appKey !== '', $appKey !== '' ? 'APP_KEY is set.' : 'Missing APP_KEY.', [
            'env' => app()->environment(),
        ]);

        // Pepper requirements
        $pepperRequired = (bool) config('security.require_password_pepper', false);
        $pepper = (string) (config('security.password_pepper') ?: '');
        $pepperPrev = (string) (config('security.password_pepper_previous') ?: '');
        $add('auth.password_pepper', ! $pepperRequired || $pepper !== '', $pepperRequired
            ? ($pepper !== '' ? 'Password pepper is set (required).' : 'Password pepper is required but missing.')
            : ($pepper !== '' ? 'Password pepper is set (optional).' : 'Password pepper not set (optional).'), [
            'required' => $pepperRequired,
            'previous_set' => $pepperPrev !== '',
        ]);

        // Session/cache drivers
        $sessionDriver = (string) config('session.driver');
        $cacheStore = (string) config('cache.default');
        $redisEnabled = (bool) env('REDIS_ENABLED', false);

        $add('session.driver', $sessionDriver !== '', "Session driver: {$sessionDriver}", [
            'redis_enabled' => $redisEnabled,
        ]);
        $add('cache.store', $cacheStore !== '', "Cache store: {$cacheStore}", [
            'redis_enabled' => $redisEnabled,
        ]);

        // DB-backed session/cache tables if used
        if ($sessionDriver === 'database') {
            $add('session.table', Schema::hasTable('sessions'), Schema::hasTable('sessions')
                ? 'sessions table exists.'
                : 'sessions table is missing (run migrations).');
        }
        if ($cacheStore === 'database') {
            $add('cache.table', Schema::hasTable('cache'), Schema::hasTable('cache')
                ? 'cache table exists.'
                : 'cache table is missing (run migrations).');
        }

        // Redis connectivity (only if in use or explicitly enabled)
        $needsRedis = $redisEnabled || $sessionDriver === 'redis' || $cacheStore === 'redis';
        if ($needsRedis) {
            try {
                Cache::store('redis')->put('core:doctor', 'ok', 1);
                $value = Cache::store('redis')->get('core:doctor');
                $add('redis', $value === 'ok', $value === 'ok' ? 'Redis is reachable.' : 'Redis test failed.');
            } catch (\Throwable $e) {
                $add('redis', false, 'Redis connection failed: '.$e->getMessage());
            }
        } else {
            $add('redis', true, 'Redis not required for current configuration.');
        }

        // RBAC tables
        $rbacTables = [
            'permissions',
            'roles',
            'model_has_permissions',
            'model_has_roles',
            'role_has_permissions',
        ];
        $missingRbac = array_values(array_filter($rbacTables, static fn (string $t) => ! Schema::hasTable($t)));
        $add('rbac.tables', count($missingRbac) === 0, count($missingRbac) === 0
            ? 'RBAC tables exist.'
            : 'Missing RBAC tables: '.implode(', ', $missingRbac), [
            'missing' => $missingRbac,
        ]);

        // 2FA columns
        $twoFactorCols = ['two_factor_enabled', 'two_factor_confirmed_at', 'two_factor_secret', 'two_factor_recovery_codes'];
        $missing2fa = [];
        foreach ($twoFactorCols as $c) {
            if (! Schema::hasColumn('users', $c)) {
                $missing2fa[] = $c;
            }
        }
        $add('2fa.columns', count($missing2fa) === 0, count($missing2fa) === 0
            ? '2FA columns exist.'
            : 'Missing 2FA columns on users: '.implode(', ', $missing2fa), [
            'missing' => $missing2fa,
        ]);

        // OAuth sanity (enabled but missing creds)
        $googleEnabled = (bool) config('services.google.enabled', false);
        $googleId = (string) config('services.google.client_id', '');
        $googleSecret = (string) config('services.google.client_secret', '');
        if ($googleEnabled) {
            $add('oauth.google', $googleId !== '' && $googleSecret !== '', ($googleId !== '' && $googleSecret !== '')
                ? 'Google OAuth is enabled and credentials are set.'
                : 'Google OAuth enabled but missing GOOGLE_CLIENT_ID / GOOGLE_CLIENT_SECRET.');
        } else {
            $add('oauth.google', true, 'Google OAuth disabled.');
        }

        $facebookEnabled = (bool) config('services.facebook.enabled', false);
        $facebookId = (string) config('services.facebook.client_id', '');
        $facebookSecret = (string) config('services.facebook.client_secret', '');
        if ($facebookEnabled) {
            $add('oauth.facebook', $facebookId !== '' && $facebookSecret !== '', ($facebookId !== '' && $facebookSecret !== '')
                ? 'Facebook OAuth is enabled and credentials are set.'
                : 'Facebook OAuth enabled but missing FACEBOOK_CLIENT_ID / FACEBOOK_CLIENT_SECRET.');
        } else {
            $add('oauth.facebook', true, 'Facebook OAuth disabled.');
        }

        // Tenancy scaffold
        $tenancyEnabled = (bool) config('tenancy.enabled', false);
        if ($tenancyEnabled) {
            $hasTenants = Schema::hasTable('tenants');
            $add('tenancy.enabled', true, 'Tenancy enabled.', [
                'strategy' => (string) config('tenancy.strategy', 'subdomain'),
                'has_tenants_table' => $hasTenants,
                'spatie_teams' => (bool) config('permission.teams', false),
            ]);
            $add('tenancy.table', $hasTenants, $hasTenants ? 'tenants table exists.' : 'tenants table missing (run migrations).');
        } else {
            $add('tenancy.enabled', true, 'Tenancy disabled (single-tenant).', [
                'spatie_teams' => (bool) config('permission.teams', false),
            ]);
        }

        // Audit log storage
        $hasAudit = Schema::hasTable('audit_logs');
        $retentionDays = (int) config('security.audit.retention_days', 90);
        $add('audit.table', $hasAudit, $hasAudit ? 'audit_logs table exists.' : 'audit_logs table missing (run migrations).', [
            'retention_days' => $retentionDays,
            'prune_cmd' => 'php artisan audit:prune',
        ]);

        // Scheduler heartbeat (ops readiness)
        $heartbeatKey = 'core:scheduler:last_run';
        $last = Cache::get($heartbeatKey);
        $maxAge = (int) config('core.scheduler.heartbeat_max_age_seconds', 300);

        if (is_string($last) && $last !== '') {
            try {
                $ts = Date::parse($last);
                $age = now()->diffInSeconds($ts);
                $add('scheduler.heartbeat', $age <= $maxAge, $age <= $maxAge
                    ? 'Scheduler heartbeat is fresh.'
                    : "Scheduler heartbeat is stale ({$age}s). Ensure scheduler is running.", [
                    'last_run' => $last,
                    'max_age_seconds' => $maxAge,
                    'key' => $heartbeatKey,
                    'hint' => 'Run `php artisan schedule:run` every minute in production.',
                ]);
            } catch (\Throwable) {
                $add('scheduler.heartbeat', false, 'Scheduler heartbeat value is invalid.', [
                    'key' => $heartbeatKey,
                    'value' => $last,
                ]);
            }
        } else {
            $add('scheduler.heartbeat', true, 'No scheduler heartbeat yet (expected on fresh/local setups).', [
                'key' => $heartbeatKey,
                'max_age_seconds' => $maxAge,
                'hint' => 'In production, ensure scheduler is running so this becomes fresh.',
            ]);
        }

        $okAll = ! collect($checks)->contains(static fn (array $c) => $c['ok'] === false);

        if ((bool) $this->option('json')) {
            $this->line(json_encode([
                'ok' => $okAll,
                'checks' => $checks,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        } else {
            foreach ($checks as $c) {
                $this->line(($c['ok'] ? '[OK] ' : '[FAIL] ').$c['name'].' - '.$c['message']);
            }
            $this->newLine();
            $this->info($okAll ? 'core:doctor OK' : 'core:doctor FAILED');
        }

        return $okAll ? self::SUCCESS : self::FAILURE;
    }
}


