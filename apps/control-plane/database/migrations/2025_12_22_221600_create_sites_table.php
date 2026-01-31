<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('key', 64);
            $table->string('name', 191);
            $table->string('domain', 255)->nullable();
            $table->string('status', 32)->default('active'); // active, suspended, archived
            $table->timestamps();

            // Unique constraint: key must be unique per tenant
            $table->unique(['tenant_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sites');
    }
};

