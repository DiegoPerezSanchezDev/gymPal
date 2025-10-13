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
        Schema::create('connections', function (Blueprint $table) {
            $table->id();
            
            // Quién ENVÍA la solicitud ("el emisor")
            // Si el usuario emisor es borrado, la solicitud también se borra.
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            
            // Quién RECIBE la solicitud ("el receptor")
            // Si el usuario receptor es borrado, la solicitud también se borra.
            $table->foreignId('receiver_id')->constrained('users')->onDelete('cascade');
            
            // El estado de la conexión. Empieza siempre como 'pending'.
            $table->enum('status', ['pending', 'accepted', 'rejected', 'blocked'])->default('pending');
            
            $table->timestamps();

            // Un mismo usuario solo puede enviar una solicitud a otro. Evita duplicados.
            $table->unique(['sender_id', 'receiver_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('connections');
    }
};