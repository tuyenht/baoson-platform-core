<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();

            $table->string('event', 191);
            $table->timestamp('occurred_at')->useCurrent();

            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('actor_email', 191)->nullable();

            $table->string('ip', 64)->nullable();
            $table->string('user_agent', 512)->nullable();

            $table->string('method', 16)->nullable();
            $table->string('route', 191)->nullable();
            $table->string('path', 512)->nullable();

            $table->unsignedSmallInteger('status_code')->nullable();

            $table->json('meta')->nullable();

            $table->index(['occurred_at', 'event']);
            $table->index(['actor_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};


