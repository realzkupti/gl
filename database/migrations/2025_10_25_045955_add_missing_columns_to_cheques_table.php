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

        // เช็คว่ามี table อยู่จริงก่อน
        if ($schema->hasTable('cheques')) {
            $schema->table('cheques', function (Blueprint $table) use ($schema) {
                // เพิ่ม created_at ถ้ายังไม่มี
                if (!$schema->hasColumn('cheques', 'created_at')) {
                    $table->timestamp('created_at')->nullable()->default(now());
                }

                // เพิ่ม updated_at ถ้ายังไม่มี
                if (!$schema->hasColumn('cheques', 'updated_at')) {
                    $table->timestamp('updated_at')->nullable()->default(now());
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $schema = Schema::connection('pgsql');

        if ($schema->hasTable('cheques')) {
            $schema->table('cheques', function (Blueprint $table) use ($schema) {
                if ($schema->hasColumn('cheques', 'created_at')) {
                    $table->dropColumn('created_at');
                }

                if ($schema->hasColumn('cheques', 'updated_at')) {
                    $table->dropColumn('updated_at');
                }
            });
        }
    }
};
