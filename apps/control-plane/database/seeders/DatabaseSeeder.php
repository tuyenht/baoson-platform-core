<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RbacSeeder::class,
            FeatureFlagsSeeder::class,
            AdminMenuItemsSeeder::class,
        ]);

        // Deterministic seeded accounts (idempotent) for verification.
        // Only create in local/testing to avoid accidental production credentials.
        //
        // IMPORTANT: do NOT Hash::make() here, because this project supports optional
        // server-side peppering via User::setPasswordAttribute().
        //
        // NOTE: Demo users are created with NEW roles (saasOwner, saasAdmin, etc.)
        // Old roles (super_admin, admin, editor, viewer) are deprecated.
        if (app()->environment(['local', 'testing'])) {
            $saasOwner = User::updateOrCreate(
                ['email' => 'saasowner@baoson.net'],
                ['name' => 'SaaS Owner', 'password' => 'saas@owner26@', 'email_verified_at' => now()]
            );
            if (! $saasOwner->hasRole('saasOwner')) {
                $saasOwner->assignRole('saasOwner');
            }

            $saasAdmin = User::updateOrCreate(
                ['email' => 'saasadmin@baoson.net'],
                ['name' => 'SaaS Admin', 'password' => 'saas@admin26@', 'email_verified_at' => now()]
            );
            if (! $saasAdmin->hasRole('saasAdmin')) {
                $saasAdmin->assignRole('saasAdmin');
            }

            $siteOwner = User::updateOrCreate(
                ['email' => 'siteowner@baoson.net'],
                ['name' => 'Site Owner', 'password' => 'site@owner26@', 'email_verified_at' => now()]
            );
            if (! $siteOwner->hasRole('siteOwner')) {
                $siteOwner->assignRole('siteOwner');
            }

            $siteAdmin = User::updateOrCreate(
                ['email' => 'siteadmin@baoson.net'],
                ['name' => 'Site Admin', 'password' => 'site@admin26@', 'email_verified_at' => now()]
            );
            if (! $siteAdmin->hasRole('siteAdmin')) {
                $siteAdmin->assignRole('siteAdmin');
            }

            $siteEditor = User::updateOrCreate(
                ['email' => 'siteeditor@baoson.net'],
                ['name' => 'Site Editor', 'password' => 'site@editor26^', 'email_verified_at' => now()]
            );
            if (! $siteEditor->hasRole('siteEditor')) {
                $siteEditor->assignRole('siteEditor');
            }

            $siteViewer = User::updateOrCreate(
                ['email' => 'siteviewer@baoson.net'],
                ['name' => 'Site Viewer', 'password' => 'site@viewer26^', 'email_verified_at' => now()]
            );
            if (! $siteViewer->hasRole('siteViewer')) {
                $siteViewer->assignRole('siteViewer');
            }
        }
    }
}
