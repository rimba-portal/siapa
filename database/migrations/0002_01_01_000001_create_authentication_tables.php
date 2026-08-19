<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_auths', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete()->unique();
            $table->string('auth_provider', 50);
            $table->string('auth_identifier');
            // Password recovery.
            $table->text('totp_secret')->nullable();
            $table->text('totp_recovery_codes')->nullable();
            // Face Authentication.
            $table->text('face_descriptor')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamp('last_face_auth_at')->nullable();
            // Onboarding.
            $table->boolean('setup_completed')->default(false);
            $table->timestamps();

            $table->unique(['auth_provider', 'auth_identifier']);
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
