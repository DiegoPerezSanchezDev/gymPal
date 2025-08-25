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
            // Usamos DECIMAL para una alta precisión en las coordenadas GPS.
            // Son opcionales (nullable) ya que el usuario puede no dar permiso de geolocalización.
            $table->decimal('latitude', 10, 7)->nullable()->after('location_city');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
        });
    }

    /**
     * Revierte las migraciones.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Borramos ambas columnas.
            $table->dropColumn(['latitude', 'longitude']);
        });
    }
};