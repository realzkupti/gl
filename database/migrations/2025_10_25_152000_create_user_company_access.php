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
        // สร้างตาราง sys_user_company_access
        // ผู้ใช้แต่ละคนสามารถเข้าถึง Company (ฐานข้อมูล) ไหนได้บ้าง
        Schema::connection('pgsql')->create('sys_user_company_access', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('company_id');
            $table->timestamps();

            // Foreign keys
            $table->foreign('user_id')
                ->references('id')
                ->on('sys_users')
                ->onDelete('cascade');

            $table->foreign('company_id')
                ->references('id')
                ->on('sys_companies')
                ->onDelete('cascade');

            // Unique constraint (user ไม่สามารถมี access ซ้ำกับ company เดียวกัน)
            $table->unique(['user_id', 'company_id']);
        });

        // Update sys_user_menu_company_access ถ้ามี (เปลี่ยนชื่อให้ตรงกับ convention)
        // ตารางนี้ใช้สำหรับจำกัดว่า user เข้าถึงเมนูไหนได้บ้างในแต่ละ company
        if (Schema::connection('pgsql')->hasTable('sys_user_menu_company_access')) {
            // ตารางนี้มีอยู่แล้วจาก migration ก่อนหน้า
            // ไม่ต้องทำอะไร
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('pgsql')->dropIfExists('sys_user_company_access');
    }
};
