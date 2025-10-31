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

        if (!$schema->hasTable('user_menu_company_access')) {
            $schema->create('user_menu_company_access', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->comment('User ID');
                $table->unsignedBigInteger('menu_id')->comment('Menu ID (BPLUS menus only)');
                $table->unsignedBigInteger('company_id')->comment('Company ID that user can access for this menu');
                $table->timestamps();

                // Foreign keys
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('menu_id')->references('id')->on('menus')->onDelete('cascade');
                $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');

                // Unique constraint
                $table->unique(['user_id', 'menu_id', 'company_id'], 'user_menu_company_unique');

                // Indexes
                $table->index('user_id');
                $table->index('menu_id');
                $table->index('company_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('pgsql')->dropIfExists('user_menu_company_access');
    }
};
