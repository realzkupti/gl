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
        $conn = DB::connection('pgsql');

        // 1. Rename system tables
        Schema::connection('pgsql')->rename('menu_groups', 'sys_departments');
        Schema::connection('pgsql')->rename('users', 'sys_users');
        Schema::connection('pgsql')->rename('menus', 'sys_menus');
        Schema::connection('pgsql')->rename('user_menu_permissions', 'sys_user_menu_permissions');
        Schema::connection('pgsql')->rename('companies', 'sys_companies');
        if (Schema::connection('pgsql')->hasTable('activity_logs') && !Schema::connection('pgsql')->hasTable('sys_activity_logs')) {
            Schema::connection('pgsql')->rename('activity_logs', 'sys_activity_logs');
        }

        // 2. Rename cheque tables (ยังใช้งานอยู่)
        if (Schema::connection('pgsql')->hasTable('cheques')) {
            Schema::connection('pgsql')->rename('cheques', 'sys_cheques');
        }
        if (Schema::connection('pgsql')->hasTable('cheque_templates')) {
            Schema::connection('pgsql')->rename('cheque_templates', 'sys_cheque_templates');
        }
        if (Schema::connection('pgsql')->hasTable('branches')) {
            Schema::connection('pgsql')->rename('branches', 'sys_branches');
        }

        // 3. Rename user_menu_company_access
        if (Schema::connection('pgsql')->hasTable('user_menu_company_access')) {
            Schema::connection('pgsql')->rename('user_menu_company_access', 'sys_user_menu_company_access');
        }

        // 4. Update column names using raw SQL
        // menu_group_id -> department_id
        if (Schema::connection('pgsql')->hasColumn('sys_menus', 'menu_group_id')) {
            $conn->statement('ALTER TABLE sys_menus RENAME COLUMN menu_group_id TO department_id');
        }

        // 5. Drop role-related tables (ไม่ใช้แล้ว)
        Schema::connection('pgsql')->dropIfExists('role_menu_permissions');
        Schema::connection('pgsql')->dropIfExists('user_roles');
        Schema::connection('pgsql')->dropIfExists('roles');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverse column rename using raw SQL
        if (Schema::connection('pgsql')->hasColumn('sys_menus', 'department_id')) {
            DB::connection('pgsql')->statement('ALTER TABLE sys_menus RENAME COLUMN department_id TO menu_group_id');
        }

        // Reverse table renames
        Schema::connection('pgsql')->rename('sys_user_menu_company_access', 'user_menu_company_access');
        Schema::connection('pgsql')->rename('sys_branches', 'branches');
        Schema::connection('pgsql')->rename('sys_cheque_templates', 'cheque_templates');
        Schema::connection('pgsql')->rename('sys_cheques', 'cheques');
        if (Schema::connection('pgsql')->hasTable('sys_activity_logs') && !Schema::connection('pgsql')->hasTable('activity_logs')) {
            Schema::connection('pgsql')->rename('sys_activity_logs', 'activity_logs');
        }
        Schema::connection('pgsql')->rename('sys_companies', 'companies');
        Schema::connection('pgsql')->rename('sys_user_menu_permissions', 'user_menu_permissions');
        Schema::connection('pgsql')->rename('sys_menus', 'menus');
        Schema::connection('pgsql')->rename('sys_users', 'users');
        Schema::connection('pgsql')->rename('sys_departments', 'menu_groups');

        // Note: roles tables won't be recreated in rollback
    }
};
