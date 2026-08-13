<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_auth', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->unique();
            $table->string('username')->nullable();
            $table->string('auth_provider', 50)->nullable();
            $table->string('auth_identifier')->nullable();
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();
            $table->text('face_descriptor')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamp('last_face_auth_at')->nullable();
            $table->boolean('setup_completed')->default(false);
            $table->timestamps();
            $table->index(['auth_provider', 'auth_identifier']);
        });

        Schema::create('authentication_attempts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('provider', 50);
            $table->string('identifier');
            $table->string('event', 100);
            $table->boolean('success');
            $table->string('message')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            $table->index(['provider', 'success']);
            $table->index(['identifier', 'created_at']);
            $table->index(['event', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('authentication_attempts');
        Schema::dropIfExists('user_auth');
    }
};
