<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Cambiamos el tipo de la columna a JSON
            $table->json('availability_general')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Para poder revertir, la volvemos a cambiar a string
            $table->string('availability_general')->nullable()->change();
        });
    }
};