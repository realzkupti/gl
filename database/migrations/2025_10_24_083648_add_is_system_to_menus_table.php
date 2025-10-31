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
        if ($schema->hasTable('menus') && !$schema->hasColumn('menus', 'is_system')) {
            $schema->table('menus', function (Blueprint $table) {
                $table->boolean('is_system')->default(false)->after('is_active')
                    ->comment('System menu that cannot be deleted');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $schema = Schema::connection('pgsql');
        if ($schema->hasTable('menus') && $schema->hasColumn('menus', 'is_system')) {
            $schema->table('menus', function (Blueprint $table) {
                $table->dropColumn('is_system');
            });
        }
    }
};
