<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table): void {
            $table->id();

            $table->string('name');
            $table->string('guard_name');
            $table->string('description')->nullable();

            $table->timestamps();

            $table->unique(['name', 'guard_name']);
        });

        Schema::create('roles', function (Blueprint $table): void {
            $table->id();

            $table->string('name');
            $table->string('guard_name');
            $table->string('description')->nullable();

            $table->timestamps();

            $table->unique(['name', 'guard_name']);
        });

        Schema::create('model_has_permissions', function (Blueprint $table): void {
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();

            $table->string('model_type');
            $table->unsignedBigInteger('model_id');

            $table->foreignId('team_id');

            $table->primary([
                'team_id',
                'permission_id',
                'model_id',
                'model_type',
            ]);

            $table->index('team_id');
            $table->index(['model_id', 'model_type']);
        });

        Schema::create('model_has_roles', function (Blueprint $table): void {
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();

            $table->string('model_type');
            $table->unsignedBigInteger('model_id');

            $table->foreignId('team_id');

            $table->primary([
                'team_id',
                'role_id',
                'model_id',
                'model_type',
            ]);

            $table->index('team_id');
            $table->index(['model_id', 'model_type']);
        });

        Schema::create('role_has_permissions', function (Blueprint $table): void {
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();

            $table->foreignId('role_id')->constrained()->cascadeOnDelete();

            $table->primary([
                'permission_id',
                'role_id',
            ]);
        });

        Schema::create('model_access_controls', function (Blueprint $table): void {
            $table->id();

            $table->morphs('model');

            $table->string('action');
            $table->string('role');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('model_access_controls');
        Schema::dropIfExists('role_has_permissions');
        Schema::dropIfExists('model_has_roles');
        Schema::dropIfExists('model_has_permissions');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('permissions');
    }
};
