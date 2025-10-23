<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $schema = Schema::connection('pgsql');
        if ($schema->hasTable('menus')) { return; }
        $schema->create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('label');
            $table->string('route')->nullable();
            $table->string('url')->nullable();
            $table->string('icon')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable()->index();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            // Optional: simple role tags (comma separated) for demo; in real apps, use pivot tables
            $table->string('roles')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('pgsql')->dropIfExists('menus');
    }
};
