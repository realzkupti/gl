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

        if (!$schema->hasTable('companies')) {
            $schema->create('companies', function (Blueprint $table) {
                $table->id();
                $table->string('key', 50)->unique()->comment('Company identifier key (default, JUNE, KSIIB, etc.)');
                $table->string('label', 255)->comment('Company display name');
                $table->string('driver', 20)->default('sqlsrv')->comment('Database driver (sqlsrv, mysql, pgsql)');
                $table->string('host', 255)->comment('Database host');
                $table->string('port', 10)->comment('Database port');
                $table->string('database', 100)->comment('Database name');
                $table->string('username', 100)->comment('Database username');
                $table->string('password', 255)->comment('Database password (encrypted)');
                $table->string('charset', 20)->default('utf8')->comment('Database charset');
                $table->string('collation', 50)->nullable()->comment('Database collation');
                $table->boolean('is_active')->default(true)->comment('Is company active');
                $table->integer('sort_order')->default(0)->comment('Display order');
                $table->timestamps();

                $table->index('is_active');
                $table->index('sort_order');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('pgsql')->dropIfExists('companies');
    }
};
