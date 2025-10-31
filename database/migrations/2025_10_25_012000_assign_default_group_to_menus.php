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
        $conn = DB::connection('pgsql');

        // Get default group ID
        $defaultGroup = $conn->table('menu_groups')->where('key', 'default')->first();

        if ($defaultGroup) {
            // Assign default group to menus without a group
            $conn->table('menus')->whereNull('menu_group_id')->update(['menu_group_id' => $defaultGroup->id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse as it's just assigning default
    }
};