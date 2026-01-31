<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class PasswordRehashAllUsers extends Command
{
    protected $signature = 'password:check-rotation
        {--dry-run : Show status without making changes}';

    protected $description = 'Check password rotation status and provide guidance for pepper rotation.';

    public function handle(): int
    {
        $globalPepper = (string) (config('security.password_pepper') ?: '');
        $previousPepper = (string) (config('security.password_pepper_previous') ?: '');

        $this->info('=== PASSWORD PEPPER ROTATION STATUS ===');
        $this->newLine();

        // Check configuration
        $this->info('Configuration:');
        $this->line('  AUTH_PASSWORD_PEPPER: ' . ($globalPepper !== '' ? '✅ Set' : '❌ Not set'));
        $this->line('  AUTH_PASSWORD_PEPPER_PREVIOUS: ' . ($previousPepper !== '' ? '✅ Set (rotation active)' : '❌ Not set'));
        $this->newLine();

        if ($globalPepper === '') {
            $this->error('AUTH_PASSWORD_PEPPER is not set.');
            $this->warn('Set AUTH_PASSWORD_PEPPER in .env file.');
            return self::FAILURE;
        }

        $users = User::all();
        $total = $users->count();

        $this->info("Total users: {$total}");
        $this->newLine();

        if ($previousPepper === '') {
            $this->warn('⚠️  AUTH_PASSWORD_PEPPER_PREVIOUS is not set.');
            $this->newLine();
            $this->warn('If you just changed AUTH_PASSWORD_PEPPER:');
            $this->warn('  → All existing passwords will NOT work with new pepper');
            $this->warn('  → Users must reset their passwords');
            $this->newLine();
            $this->info('To enable zero-downtime rotation:');
            $this->line('  1. Set AUTH_PASSWORD_PEPPER_PREVIOUS = old pepper value');
            $this->line('  2. Restart application');
            $this->line('  3. Users can login with old pepper, passwords auto re-hash on login');
            $this->newLine();
        } else {
            $this->info('✅ Rotation is active (AUTH_PASSWORD_PEPPER_PREVIOUS is set).');
            $this->newLine();
            $this->info('How it works:');
            $this->line('  → Users can login with old pepper');
            $this->line('  → Passwords automatically re-hash with new pepper on login');
            $this->line('  → Zero downtime - no user action required');
            $this->newLine();
            $this->info('After all users have logged in (few weeks/months):');
            $this->line('  → Remove AUTH_PASSWORD_PEPPER_PREVIOUS');
            $this->line('  → Restart application');
            $this->newLine();
        }

        // Show user-specific pepper info
        if ($total > 0) {
            $this->info('User-specific pepper examples:');
            $sampleUsers = $users->take(3);
            foreach ($sampleUsers as $user) {
                $userPepper = $this->getUserSpecificPepper($user, $globalPepper);
                $this->line("  {$user->email}: " . substr($userPepper, 0, 16) . '...');
            }
            $this->newLine();
            $this->info('Note: Each user has a unique pepper (based on email + ID).');
            $this->info('      This prevents hash copying between users.');
        }

        return self::SUCCESS;
    }

    /**
     * Get user-specific pepper (same logic as User model).
     */
    private function getUserSpecificPepper(User $user, string $globalPepper): string
    {
        if ($globalPepper === '') {
            return '';
        }

        $email = (string) ($user->email ?? '');
        $userId = $user->id ?? 0;
        $userIdentifier = $email . '|' . $userId;

        return hash_hmac('sha256', $userIdentifier, $globalPepper);
    }
}

