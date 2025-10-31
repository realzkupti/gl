<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Rename column from department_id to system_type
        Schema::table('sys_menus', function (Blueprint $table) {
            $table->renameColumn('department_id', 'system_type');
        });

        // Step 2: Migrate data based on connection_type
        // pgsql → system_type = 1 (System)
        // mysql → system_type = 2 (Bplus)
        DB::table('sys_menus')
            ->where('connection_type', 'pgsql')
            ->update(['system_type' => 1]);

        DB::table('sys_menus')
            ->where('connection_type', 'mysql')
            ->update(['system_type' => 2]);

        // Step 3: Update sys_departments to have only base system types
        DB::table('sys_departments')->truncate();

        DB::table('sys_departments')->insert([
            [
                'id' => 1,
                'key' => 'system',
                'label' => 'ระบบ',
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'key' => 'bplus',
                'label' => 'Bplus',
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'key' => 'admin',
                'label' => 'Admin',
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'key' => 'user',
                'label' => 'User',
                'sort_order' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sys_menus', function (Blueprint $table) {
            $table->renameColumn('system_type', 'department_id');
        });

        // Note: sys_departments data cannot be fully restored
        // Manual intervention required if rollback is needed
    }
};
