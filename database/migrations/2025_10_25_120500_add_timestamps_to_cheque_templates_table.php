<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $schema = Schema::connection('pgsql');

        if (!$schema->hasColumn('cheque_templates', 'created_at')) {
            Schema::connection('pgsql')->table('cheque_templates', function (Blueprint $table) {
                $table->timestamp('created_at')->nullable();
            });
        }

        if (!$schema->hasColumn('cheque_templates', 'updated_at')) {
            Schema::connection('pgsql')->table('cheque_templates', function (Blueprint $table) {
                $table->timestamp('updated_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        $schema = Schema::connection('pgsql');

        if ($schema->hasColumn('cheque_templates', 'updated_at')) {
            Schema::connection('pgsql')->table('cheque_templates', function (Blueprint $table) {
                $table->dropColumn('updated_at');
            });
        }

        if ($schema->hasColumn('cheque_templates', 'created_at')) {
            Schema::connection('pgsql')->table('cheque_templates', function (Blueprint $table) {
                $table->dropColumn('created_at');
            });
        }
    }
};

