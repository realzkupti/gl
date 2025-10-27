<?php

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
        // Add logo column to sys_companies table
        Schema::connection('pgsql')->table('sys_companies', function (Blueprint $table) {
            $table->string('logo', 255)->nullable()->after('label');
        });

        // Add has_sticky_note column to sys_menus table
        Schema::connection('pgsql')->table('sys_menus', function (Blueprint $table) {
            $table->boolean('has_sticky_note')->default(false)->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('pgsql')->table('sys_companies', function (Blueprint $table) {
            $table->dropColumn('logo');
        });

        Schema::connection('pgsql')->table('sys_menus', function (Blueprint $table) {
            $table->dropColumn('has_sticky_note');
        });
    }
};
