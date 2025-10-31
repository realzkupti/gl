<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $schema = Schema::connection('pgsql');
        if (!$schema->hasTable('user_menu_permissions')) {
            $schema->create('user_menu_permissions', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->bigInteger('user_id')->index();
                $table->bigInteger('menu_id')->index();
                $table->boolean('can_view')->default(false);
                $table->boolean('can_create')->default(false);
                $table->boolean('can_update')->default(false);
                $table->boolean('can_delete')->default(false);
                $table->boolean('can_export')->default(false);
                $table->boolean('can_approve')->default(false);
                $table->timestamps();

                $table->unique(['user_id', 'menu_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::connection('pgsql')->dropIfExists('user_menu_permissions');
    }
};
