<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gym_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('gym_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            // Evitar duplicados: un usuario solo puede unirse una vez al mismo gym (aunque puede estar en varios)
            $table->unique(['user_id', 'gym_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gym_user');
    }
};
