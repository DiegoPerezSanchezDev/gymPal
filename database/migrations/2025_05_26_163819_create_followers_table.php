<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('followers', function (Blueprint $table) {
            $table->id();
            // El usuario que realiza la acción de seguir
            $table->foreignId('follower_id')->constrained('users')->onDelete('cascade');
            // El usuario que está siendo seguido
            $table->foreignId('following_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['follower_id', 'following_id']); // Evita duplicados
        });
    }
    public function down(): void { Schema::dropIfExists('followers'); }
};
