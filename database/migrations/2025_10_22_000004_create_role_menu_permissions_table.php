<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $schema = Schema::connection('pgsql');
        if (!$schema->hasTable('role_menu_permissions')) {
            $schema->create('role_menu_permissions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('menu_id')->constrained('menus')->cascadeOnDelete();
            $table->boolean('can_view')->default(false);
            $table->boolean('can_create')->default(false);
            $table->boolean('can_update')->default(false);
            $table->boolean('can_delete')->default(false);
            $table->boolean('can_export')->default(false);
            $table->boolean('can_approve')->default(false);
            $table->timestamps();
            $table->unique(['role_id', 'menu_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::connection('pgsql')->dropIfExists('role_menu_permissions');
    }
};
