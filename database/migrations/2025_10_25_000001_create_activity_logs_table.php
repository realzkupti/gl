<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $schema = Schema::connection('pgsql');

        // If legacy table exists and new one does not, rename first
        if ($schema->hasTable('activity_logs') && !$schema->hasTable('sys_activity_logs')) {
            $schema->rename('activity_logs', 'sys_activity_logs');
        }

        // Ensure new table exists with correct name
        if (!$schema->hasTable('sys_activity_logs')) {
            $schema->create('sys_activity_logs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('action', 255)->nullable();
                $table->string('url', 2048);
                $table->string('method', 10);
                $table->string('ip', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->json('payload')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        $schema = Schema::connection('pgsql');
        if ($schema->hasTable('sys_activity_logs')) {
            $schema->drop('sys_activity_logs');
        }
    }
};
