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
        Schema::table('users', function (Blueprint $table) {
            // El campo 'name' ya existe, lo usaremos como el nombre a mostrar inicialmente.
            // Podrías renombrarlo o añadir 'display_name' si necesitas ambos.

            // Username único para URLs de perfil y potencialmente login (opcional pero recomendado)
            $table->string('username')->unique()->nullable()->after('name'); // O después de 'email' si prefieres

            // Nombre a mostrar públicamente (si quieres que sea diferente del 'name' de login o 'username')
            $table->string('display_name')->nullable()->after('username');

            // URL de la foto de perfil
            $table->string('profile_picture_url')->nullable()->after('display_name');

            // Biografía corta del usuario
            $table->text('bio')->nullable()->after('profile_picture_url');

            // Ciudad del usuario (para búsqueda y mostrar en perfil)
            $table->string('location_city')->nullable()->after('bio');

            // Disponibilidad general para entrenar (podría ser un string o JSON)
            // Si es JSON, considera un tipo de dato text y casteo en el modelo.
            // Por simplicidad, lo dejaremos como string por ahora.
            $table->string('availability_general')->nullable();
            // Nivel de experiencia en fitness
            $table->string('experience_level')->nullable(); // ej: 'Principiante', 'Intermedio', 'Avanzado'
        });
    }

    /**
     * Reverse the migrations.
     * (Para poder deshacer los cambios si es necesario)
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'username',
                'display_name',
                'profile_picture_url',
                'bio',
                'location_city',
                'availability_general',
                'experience_level',
            ]);
        });
    }
};