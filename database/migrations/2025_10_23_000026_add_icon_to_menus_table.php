<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $schema = Schema::connection('pgsql');

        if ($schema->hasTable('menus') && !$schema->hasColumn('menus', 'icon')) {
            $schema->table('menus', function (Blueprint $table) {
                $table->string('icon', 100)->nullable()->after('route_name');
            });
        }
    }

    public function down(): void
    {
        $schema = Schema::connection('pgsql');

        if ($schema->hasTable('menus') && $schema->hasColumn('menus', 'icon')) {
            $schema->table('menus', function (Blueprint $table) {
                $table->dropColumn('icon');
            });
        }
    }
};
