<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $schema = Schema::connection('pgsql');
        if (!$schema->hasTable('roles')) {
            $schema->create('roles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 100)->unique();
            $table->string('description', 255)->nullable();
            $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::connection('pgsql')->dropIfExists('roles');
    }
};
