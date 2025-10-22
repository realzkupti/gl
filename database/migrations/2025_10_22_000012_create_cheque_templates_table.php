<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('cheque_templates')) {
            Schema::create('cheque_templates', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('bank', 50);
                $table->jsonb('template_json');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cheque_templates');
    }
};

