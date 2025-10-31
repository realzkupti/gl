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
        Schema::table('menus', function (Blueprint $table) {
            if (Schema::hasColumn('menus', 'menu_group')) {
                $table->dropColumn('menu_group');
            }
            $table->foreignId('menu_group_id')->nullable()->constrained('menu_groups')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->dropForeign(['menu_group_id']);
            $table->dropColumn('menu_group_id');
            $table->string('menu_group', 50)->default('default')->after('is_active')
                ->comment('Menu group for permission separation (default, bplus, etc.)');
            $table->index('menu_group');
        });
    }
};
