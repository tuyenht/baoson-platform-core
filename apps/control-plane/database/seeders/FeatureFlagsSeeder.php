<?php

namespace Database\Seeders;

use App\Models\FeatureFlag;
use Illuminate\Database\Seeder;

class FeatureFlagsSeeder extends Seeder
{
    /**
     * Seed baseline feature flags.
     * These flags control visibility/access to major features.
     */
    public function run(): void
    {
        $flags = [
            [
                'key' => 'admin.dashboard',
                'enabled' => true,
                'scope' => 'global',
                'description' => 'Admin dashboard feature',
            ],
            [
                'key' => 'admin.users',
                'enabled' => true,
                'scope' => 'global',
                'description' => 'User management feature',
            ],
            [
                'key' => 'admin.roles',
                'enabled' => true,
                'scope' => 'global',
                'description' => 'Role management feature',
            ],
            [
                'key' => 'admin.permissions',
                'enabled' => true,
                'scope' => 'global',
                'description' => 'Permission management feature',
            ],
            [
                'key' => 'admin.tenants',
                'enabled' => true,
                'scope' => 'global',
                'description' => 'Tenant management feature',
            ],
            [
                'key' => 'admin.settings',
                'enabled' => true,
                'scope' => 'global',
                'description' => 'Settings management feature',
            ],
            [
                'key' => 'admin.audit',
                'enabled' => true,
                'scope' => 'global',
                'description' => 'Audit log feature',
            ],
            [
                'key' => 'site.content',
                'enabled' => true,
                'scope' => 'global',
                'description' => 'Site content management feature',
            ],
            [
                'key' => 'site.media',
                'enabled' => true,
                'scope' => 'global',
                'description' => 'Site media management feature',
            ],
            [
                'key' => 'site.i18n',
                'enabled' => true,
                'scope' => 'global',
                'description' => 'Site internationalization feature',
            ],
        ];

        foreach ($flags as $flag) {
            FeatureFlag::updateOrCreate(
                ['key' => $flag['key'], 'scope' => $flag['scope']],
                $flag
            );
        }
    }
}

