<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feature_flags', function (Blueprint $table) {
            $table->id();
            $table->string('key', 128)->unique();
            $table->boolean('enabled')->default(true);
            $table->string('scope', 32)->default('global'); // global, tenant, site, user
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['enabled', 'scope']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feature_flags');
    }
};

