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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            
            // Usuario que recibe la notificación
            $table->foreignId('user_id')
                ->constrained('users')
                ->onDelete('cascade');
            
            // Tipo de notificación
            $table->string('type'); // post_shared, new_message, connection_accepted, post_liked, post_commented
            
            // Título y mensaje de la notificación
            $table->string('title');
            $table->text('message');
            
            // Datos adicionales en JSON (información del post, mensaje, usuario, etc.)
            $table->json('data')->nullable();
            
            // Relación polimórfica para vincular con posts, mensajes, conexiones, etc.
            $table->morphs('notifiable'); // Crea notifiable_id y notifiable_type
            
            // Si la notificación ha sido leída
            $table->timestamp('read_at')->nullable();
            
            $table->timestamps();
            
            // Índices para mejorar rendimiento
            $table->index(['user_id', 'read_at']);
            $table->index(['user_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
