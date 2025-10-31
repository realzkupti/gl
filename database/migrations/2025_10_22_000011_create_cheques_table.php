<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $schema = Schema::connection('pgsql');
        if (!$schema->hasTable('cheques')) {
            $schema->create('cheques', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('branch_code', 50)->nullable();
                $table->string('bank', 50);
                $table->string('cheque_number', 50);
                $table->date('date');
                $table->string('payee', 255);
                $table->decimal('amount', 15, 2);
                $table->timestamp('printed_at')->nullable();
                $table->timestamps();
                $table->index(['cheque_number']);
            });
        }
    }

    public function down(): void
    {
        Schema::connection('pgsql')->dropIfExists('cheques');
    }
};
