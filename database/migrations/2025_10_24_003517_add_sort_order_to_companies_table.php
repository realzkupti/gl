<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $schema = Schema::connection('pgsql');

        if ($schema->hasTable('companies') && !$schema->hasColumn('companies', 'sort_order')) {
            $schema->table('companies', function (Blueprint $table) {
                $table->integer('sort_order')->default(0);
                $table->index('sort_order');
            });
        }
    }

    public function down(): void
    {
        $schema = Schema::connection('pgsql');

        if ($schema->hasTable('companies') && $schema->hasColumn('companies', 'sort_order')) {
            $schema->table('companies', function (Blueprint $table) {
                $table->dropIndex(['sort_order']);
                $table->dropColumn('sort_order');
            });
        }
    }
};
