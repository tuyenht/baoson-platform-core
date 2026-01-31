<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_menu_items', function (Blueprint $table) {
            $table->id();
            $table->string('key', 128)->unique();
            $table->string('label', 191);
            $table->string('icon', 64)->nullable();
            $table->string('route_name', 128)->nullable();
            $table->string('url', 512)->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('admin_menu_items')->cascadeOnDelete();
            $table->integer('sort_order')->default(0);
            $table->json('required_permissions')->nullable(); // Array of permission strings (ANY-of)
            $table->json('required_roles')->nullable(); // Array of role strings (ANY-of)
            $table->json('required_flags')->nullable(); // Array of feature flag keys (ALL must be enabled)
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();

            $table->index(['parent_id', 'sort_order']);
            $table->index('is_enabled');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_menu_items');
    }
};

