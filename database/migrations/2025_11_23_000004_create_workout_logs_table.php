<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workout_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('workout_id')->nullable()->constrained()->onDelete('set null'); // Nullable por si se borra la rutina
            $table->string('workout_name'); // Guardamos el nombre por si se borra la rutina
            $table->json('exercises_data'); // Snapshot de los ejercicios y series completadas
            $table->integer('duration_minutes')->nullable(); // Duración real del entrenamiento
            $table->integer('total_sets')->default(0);
            $table->integer('completed_sets')->default(0);
            $table->text('notes')->nullable(); // Notas del usuario sobre el entrenamiento
            $table->timestamps();
            
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workout_logs');
    }
};
