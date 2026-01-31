<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            // Add status column (enum-like: active, suspended, archived)
            $table->string('status', 32)->default('active')->after('is_active');
        });

        // Migrate existing is_active to status
        DB::table('tenants')->where('is_active', true)->update(['status' => 'active']);
        DB::table('tenants')->where('is_active', false)->update(['status' => 'suspended']);

        // Keep is_active for backward compatibility (will be deprecated later)
        // We don't drop it to avoid breaking existing code
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};

