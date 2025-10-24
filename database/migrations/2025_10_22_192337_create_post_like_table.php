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
    Schema::create('post_like', function (Blueprint $table) {
        $table->id();
        
        // Clave foránea para el usuario que da el like
        $table->foreignId('user_id')->constrained()->onDelete('cascade');

        // Clave foránea para el post que recibe el like
        $table->foreignId('post_id')->constrained()->onDelete('cascade');
        
        // Índice único para asegurar que un usuario solo puede dar like una vez a un post
        $table->unique(['user_id', 'post_id']);

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_like');
    }
};
