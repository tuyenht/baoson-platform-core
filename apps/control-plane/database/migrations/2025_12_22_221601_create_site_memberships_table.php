<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('sites')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('role', 32); // siteOwner, siteAdmin, siteEditor, siteTranslator, siteViewer
            $table->string('status', 32)->default('active'); // active, suspended, removed
            $table->timestamps();

            // A user can only have one membership per site
            $table->unique(['site_id', 'user_id']);
            
            // Index for efficient lookups
            $table->index(['user_id', 'status']);
            $table->index(['site_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_memberships');
    }
};

