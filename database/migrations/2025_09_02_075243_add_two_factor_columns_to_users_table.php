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
        if (!$schema->hasTable('users')) {
            return; // users table not present; nothing to add
        }
        // Avoid duplicate columns on re-run
        if (!$schema->hasColumn('users', 'two_factor_secret')) {
            $schema->table('users', function (Blueprint $table) {
                $table->text('two_factor_secret')->after('password')->nullable();
                $table->text('two_factor_recovery_codes')->after('two_factor_secret')->nullable();
                $table->timestamp('two_factor_confirmed_at')->after('two_factor_recovery_codes')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $schema = Schema::connection('pgsql');
        if (!$schema->hasTable('users')) { return; }
        // Drop only if columns exist
        $schema->table('users', function (Blueprint $table) use ($schema) {
            $drops = [];
            foreach (['two_factor_secret','two_factor_recovery_codes','two_factor_confirmed_at'] as $col) {
                if ($schema->hasColumn('users', $col)) { $drops[] = $col; }
            }
            if (!empty($drops)) {
                $table->dropColumn($drops);
            }
        });
    }
};
