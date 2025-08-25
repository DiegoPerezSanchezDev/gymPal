<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id(); //Id
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); //Id del usuario
            $table->text('content')->nullable(); //Contenido del textArea
            $table->string('image_path')->nullable(); // Para guardar la ruta de la imagen
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};