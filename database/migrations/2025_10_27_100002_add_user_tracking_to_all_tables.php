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
        $tables = [
            'sys_menus',
            'sys_menu_groups',
            'sys_departments',
            'sys_department_menu_permissions',
            'sys_user_menu_permissions',
            'sys_users',
            'sys_user_company_access',
            'companies',
            'branches',
            'cheques',
            'cheque_templates',
        ];

        foreach ($tables as $tableName) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                // Add created_by if not exists
                if (!Schema::hasColumn($tableName, 'created_by')) {
                    $table->unsignedBigInteger('created_by')->nullable()->after('created_at');
                    $table->foreign('created_by')
                          ->references('id')
                          ->on('sys_users')
                          ->onDelete('set null');
                }

                // Add updated_by if not exists
                if (!Schema::hasColumn($tableName, 'updated_by')) {
                    $table->unsignedBigInteger('updated_by')->nullable()->after('updated_at');
                    $table->foreign('updated_by')
                          ->references('id')
                          ->on('sys_users')
                          ->onDelete('set null');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'sys_menus',
            'sys_menu_groups',
            'sys_departments',
            'sys_department_menu_permissions',
            'sys_user_menu_permissions',
            'sys_users',
            'sys_user_company_access',
            'companies',
            'branches',
            'cheques',
            'cheque_templates',
        ];

        foreach ($tables as $tableName) {
            if (!Schema::hasTable($tableName)) {
                continue;
            }

            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (Schema::hasColumn($tableName, 'created_by')) {
                    $table->dropForeign(['created_by']);
                    $table->dropColumn('created_by');
                }

                if (Schema::hasColumn($tableName, 'updated_by')) {
                    $table->dropForeign(['updated_by']);
                    $table->dropColumn('updated_by');
                }
            });
        }
    }
};
