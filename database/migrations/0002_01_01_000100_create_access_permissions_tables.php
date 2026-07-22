<?php

/**
 * FOUNDATION / Access
 * Intent:
 * Enforce authorization, permissions, and segregation of duties.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        Schema::create('permissions', function (Blueprint $table): void {
            $table->bigIncrements('id'); // permission id
            $table->string('name');       // For MyISAM use string('name', 225); // (or 166 for InnoDB with Redundant/Compact row format)
            $table->string('guard_name'); // For MyISAM use string('guard_name', 25);
            $table->string('description')->nullable();
            $table->timestamps();

            $table->unique(['name', 'guard_name']);
        });
        Schema::create('roles', function (Blueprint $table): void {
            $table->bigIncrements('id'); // role id
            $table->string('name');       // For MyISAM use string('name', 225); // (or 166 for InnoDB with Redundant/Compact row format)
            $table->string('guard_name'); // For MyISAM use string('guard_name', 25);
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('model_has_permissions', function (Blueprint $table): void {
            $table->unsignedInteger('permission_id');
            $table->string('model_type');
            $table->unsignedInteger('model_id')->nullable();
            $table->unsignedInteger('team_id');
            $table->foreign('permission_id')->references('id')->on('permissions')->cascadeOnDelete();
            // Composite primary key
            $table->primary(['team_id', 'permission_id', 'model_id', 'model_type']);

            $table->index('team_id', 'model_has_permissions_team_foreign_key_index');
            $table->index(['model_id', 'model_type'], 'model_has_permissions_model_id_model_type_index');
        });
        Schema::create('model_has_roles', function (Blueprint $table): void {
            $table->unsignedInteger('role_id');
            $table->string('model_type');
            $table->unsignedInteger('model_id');
            $table->unsignedInteger('team_id')->nullable();
            $table->foreign('role_id')->references('id')->on('roles')->cascadeOnDelete();
            // Composite primary key
            $table->primary(['team_id', 'role_id', 'model_id', 'model_type']);
            // Additional indexes
            $table->index('team_id', 'model_has_roles_team_foreign_key_index');
            $table->index(['model_id', 'model_type'], 'model_has_roles_model_id_model_type_index');
        });

        Schema::create('role_has_permissions', function (Blueprint $table): void {
            $table->unsignedInteger('permission_id');
            $table->unsignedInteger('role_id');
            $table->foreign('permission_id')->references('id')->on('permissions')->cascadeOnDelete();
            $table->foreign('role_id')->references('id')->on('roles')->cascadeOnDelete();
            // Composite primary key
            $table->primary(['permission_id', 'role_id']);
        });

        Schema::create('model_access_controls', function (Blueprint $table): void {
            $table->id();
            $table->morphs('model');
            // Document, Workflow, etc.
            $table->string('action');
            // view | create | update
            $table->string('role');
            // ut_admin, st_hr, jt_manager, rt_approver
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('model_access_controls');
        Schema::dropIfExists('role_has_permissions');
        Schema::dropIfExists('model_has_roles');
        Schema::dropIfExists('model_has_permissions');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
