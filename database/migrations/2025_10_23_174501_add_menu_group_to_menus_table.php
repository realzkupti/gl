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
        $schema = Schema::connection('pgsql');

        if ($schema->hasTable('menus') && !$schema->hasColumn('menus', 'menu_group')) {
            $schema->table('menus', function (Blueprint $table) {
                $table->string('menu_group', 50)->default('default')->after('is_active')
                    ->comment('Menu group for permission separation (default, bplus, etc.)');
                $table->index('menu_group');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $schema = Schema::connection('pgsql');

        if ($schema->hasTable('menus') && $schema->hasColumn('menus', 'menu_group')) {
            $schema->table('menus', function (Blueprint $table) {
                $table->dropIndex(['menu_group']);
                $table->dropColumn('menu_group');
            });
        }
    }
};
