<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE users ALTER COLUMN availability_general TYPE json USING availability_general::json');
        } else {
            Schema::table('users', function (Blueprint $table) {
                // Cambiamos el tipo de la columna a JSON
                $table->json('availability_general')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE users ALTER COLUMN availability_general TYPE varchar USING availability_general::varchar');
        } else {
            Schema::table('users', function (Blueprint $table) {
                // Para poder revertir, la volvemos a cambiar a string
                $table->string('availability_general')->nullable()->change();
            });
        }
    }
};