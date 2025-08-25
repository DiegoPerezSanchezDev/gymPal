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
        Schema::create('conversation_user', function (Blueprint $table) {
            $table->id(); // Clave primaria para la tabla pivote (opcional, pero buena práctica)

            // Clave foránea para la tabla users
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            // Esto crea una columna user_id UNSIGNED BIGINT y la enlaza con la columna 'id' de la tabla 'users'.
            // onDelete('cascade') significa que si un usuario es eliminado, sus entradas en esta tabla pivote también se eliminarán.

            // Clave foránea para la tabla conversations
            $table->foreignId('conversation_id')->constrained('conversations')->onDelete('cascade');
            // Esto crea una columna conversation_id UNSIGNED BIGINT y la enlaza con la columna 'id' de la tabla 'conversations'.

            $table->timestamps();

            // Opcional: Si quieres que la combinación de user_id y conversation_id sea única
            // $table->unique(['user_id', 'conversation_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversation_user');
    }
};
