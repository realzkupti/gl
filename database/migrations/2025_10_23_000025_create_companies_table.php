<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $schema = Schema::connection('pgsql');

        if (!$schema->hasTable('companies')) {
            $schema->create('companies', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('key', 50)->unique();
                $table->string('label', 150);
                $table->string('driver', 20)->default('mysql'); // mysql, pgsql, sqlsrv
                $table->string('host', 150);
                $table->integer('port')->nullable();
                $table->string('database', 150);
                $table->string('username', 150);
                $table->string('password', 255)->nullable();
                $table->string('charset', 20)->nullable();
                $table->string('collation', 50)->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::connection('pgsql')->dropIfExists('companies');
    }
};
