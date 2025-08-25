<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta las migraciones.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Esta será una clave foránea que apunta a la tabla `fitness_interests`.
            // Permitirá saber si un usuario busca activamente un compañero para un deporte específico.
            // onDelete('set null') significa que si se borra el interés, el campo en el usuario se pondrá a null.
            $table->foreignId('looking_for_interest_id')
                ->nullable()
                ->after('experience_level')
                ->constrained('fitness_interests')
                ->onDelete('set null');
        });
    }

    /**
     * Revierte las migraciones.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Es importante especificar el nombre de la restricción para poder borrarla.
            // Laravel por defecto la nombra: users_looking_for_interest_id_foreign
            $table->dropForeign(['looking_for_interest_id']);
            $table->dropColumn('looking_for_interest_id');
        });
    }
};