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

        // 1. เพิ่ม department_id ใน sys_users
        Schema::connection('pgsql')->table('sys_users', function (Blueprint $table) {
            $table->unsignedBigInteger('department_id')->nullable()->after('email');
            $table->foreign('department_id')
                ->references('id')
                ->on('sys_departments')
                ->onDelete('set null');
        });

        // 2. สร้างตาราง sys_department_menu_permissions
        Schema::connection('pgsql')->create('sys_department_menu_permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('department_id');
            $table->unsignedBigInteger('menu_id');
            $table->boolean('can_view')->default(false);
            $table->boolean('can_create')->default(false);
            $table->boolean('can_update')->default(false);
            $table->boolean('can_delete')->default(false);
            $table->boolean('can_export')->default(false);
            $table->boolean('can_approve')->default(false);
            $table->timestamps();

            // Foreign keys
            $table->foreign('department_id')
                ->references('id')
                ->on('sys_departments')
                ->onDelete('cascade');

            $table->foreign('menu_id')
                ->references('id')
                ->on('sys_menus')
                ->onDelete('cascade');

            // Unique constraint
            $table->unique(['department_id', 'menu_id']);
        });

        // 3. เพิ่ม connection_type ใน sys_menus
        Schema::connection('pgsql')->table('sys_menus', function (Blueprint $table) {
            $table->string('connection_type', 20)->default('pgsql')->after('is_system');
            // pgsql = ใช้ PostgreSQL เสมอ (ระบบหลัก)
            // company = ใช้ Company Database (Bplus)
        });

        // 4. Set default department for existing users (ใช้ department 'default' ถ้ามี)
        $defaultDepartment = $conn->table('sys_departments')->where('key', 'default')->first();
        if ($defaultDepartment) {
            $conn->table('sys_users')
                ->whereNull('department_id')
                ->update(['department_id' => $defaultDepartment->id]);
        }

        // 5. Migrate role_menu_permissions → department_menu_permissions (ถ้ามีข้อมูล)
        // เนื่องจากเราลบ roles แล้ว ถ้ามีข้อมูลเก่าใน role_menu_permissions ก็ข้ามไป
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove connection_type from menus
        Schema::connection('pgsql')->table('sys_menus', function (Blueprint $table) {
            $table->dropColumn('connection_type');
        });

        // Drop department_menu_permissions table
        Schema::connection('pgsql')->dropIfExists('sys_department_menu_permissions');

        // Remove department_id from users
        Schema::connection('pgsql')->table('sys_users', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn('department_id');
        });
    }
};
