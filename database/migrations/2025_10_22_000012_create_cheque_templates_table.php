<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        $schema = Schema::connection('pgsql');
        if (!$schema->hasTable('cheque_templates')) {
            $schema->create('cheque_templates', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('bank', 50);
                $table->jsonb('template_json');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::connection('pgsql')->dropIfExists('cheque_templates');
    }
};
