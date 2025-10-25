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
        if ($schema->hasTable('cheque_templates')) {
            $schema->table('cheque_templates', function (Blueprint $table) {
                $table->unique('bank');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $schema = Schema::connection('pgsql');
        if ($schema->hasTable('cheque_templates')) {
            $schema->table('cheque_templates', function (Blueprint $table) {
                $table->dropUnique(['bank']);
            });
        }
    }
};
