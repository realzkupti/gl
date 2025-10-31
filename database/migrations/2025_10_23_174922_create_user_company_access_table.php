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

        if (!$schema->hasTable('user_company_access')) {
            $schema->create('user_company_access', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->comment('User ID from users table');
                $table->unsignedBigInteger('company_id')->comment('Company ID from companies table');
                $table->boolean('is_default')->default(false)->comment('Is this the default company for user');
                $table->timestamps();

                // Foreign keys
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');

                // Unique constraint: user can only have one record per company
                $table->unique(['user_id', 'company_id']);

                // Indexes
                $table->index('user_id');
                $table->index('company_id');
                $table->index('is_default');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('pgsql')->dropIfExists('user_company_access');
    }
};
