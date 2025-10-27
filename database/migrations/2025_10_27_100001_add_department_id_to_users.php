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
        // Check if department_id already exists
        if (!Schema::hasColumn('sys_users', 'department_id')) {
            Schema::table('sys_users', function (Blueprint $table) {
                $table->unsignedBigInteger('department_id')->nullable()->after('email');
                $table->foreign('department_id')
                      ->references('id')
                      ->on('sys_departments')
                      ->onDelete('set null');
            });
        }

        // Set default department for existing users that don't have one
        // All existing users → User department (id: 4)
        DB::table('sys_users')
            ->whereNull('department_id')
            ->update(['department_id' => 4]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sys_users', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn('department_id');
        });
    }
};
