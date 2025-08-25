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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                  ->constrained('users') // Asume que tu tabla de usuarios se llama 'users'
                  ->onDelete('cascade'); // Si un usuario es borrado, sus mensajes también

            // Clave foránea para la conversación a la que pertenece el mensaje
            $table->foreignId('conversation_id')
                  ->constrained('conversations') // Asume que tu tabla de conversaciones se llama 'conversations'
                  ->onDelete('cascade'); // Si una conversación es borrada, sus mensajes también

            $table->text('body'); // El contenido del mensaje
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            // Para el rollback, primero se elimina la restricción de clave foránea
            $table->dropForeign(['conversation_id']); // Laravel nombra la FK como nombredetabla_nombredelacolumna_foreign
            $table->dropColumn('conversation_id');
        });
    }
};
