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
        // Drop the foreign key constraint if it exists
        Schema::table('sys_menus', function (Blueprint $table) {
            // Try to drop the constraint by name
            DB::statement('ALTER TABLE sys_menus DROP CONSTRAINT IF EXISTS menus_menu_group_id_foreign');

            // Also try alternative constraint names that might exist
            DB::statement('ALTER TABLE sys_menus DROP CONSTRAINT IF EXISTS sys_menus_department_id_foreign');
            DB::statement('ALTER TABLE sys_menus DROP CONSTRAINT IF EXISTS sys_menus_system_type_foreign');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We don't want to re-add the foreign key constraint
        // as system_type is now just a categorization field, not a foreign key
    }
};
