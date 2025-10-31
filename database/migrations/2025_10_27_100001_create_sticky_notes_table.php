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
        Schema::connection('pgsql')->create('sys_sticky_notes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('menu_id');
            $table->unsignedBigInteger('company_id')->nullable(); // null = ใช้กับทุกบริษัท
            $table->text('content');
            $table->string('color', 20)->default('yellow'); // yellow, blue, green, pink, purple, gray
            $table->integer('position_x')->default(0);
            $table->integer('position_y')->default(0);
            $table->integer('width')->default(300);
            $table->integer('height')->default(180);
            $table->boolean('is_minimized')->default(false);
            $table->boolean('is_pinned')->default(false);
            $table->integer('z_index')->default(1000);
            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'menu_id', 'company_id']);
            $table->foreign('user_id')->references('id')->on('sys_users')->onDelete('cascade');
            $table->foreign('menu_id')->references('id')->on('sys_menus')->onDelete('cascade');
            $table->foreign('company_id')->references('id')->on('sys_companies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('pgsql')->dropIfExists('sys_sticky_notes');
    }
};
