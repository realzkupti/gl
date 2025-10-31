<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix sequence for sys_users table
        DB::connection('pgsql')->statement("
            SELECT setval(
                pg_get_serial_sequence('sys_users', 'id'),
                COALESCE((SELECT MAX(id) FROM sys_users), 1),
                true
            );
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse
    }
};
