<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $schema = Schema::connection('pgsql');
        if (!$schema->hasTable('users')) return;

        $exists = DB::connection('pgsql')->table('users')->where('email', 'admin@local')->exists();
        if (!$exists) {
            DB::connection('pgsql')->table('users')->insert([
                'name' => 'admin',
                'email' => 'admin@local',
                'password' => Hash::make('admin'),
                'email_verified_at' => now(),
                'remember_token' => null,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        if (!Schema::connection('pgsql')->hasTable('users')) return;
        DB::connection('pgsql')->table('users')->where('email', 'admin@local')->delete();
    }
};

