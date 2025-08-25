<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void {
        Schema::create('fitness_interests', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Ej: "Pesas", "Calistenia", "Yoga"
            $table->string('slug')->unique()->nullable(); // Opcional, para URLs amigables
            $table->timestamps();
        });
    }
    
    public function down(): void { 
        Schema::dropIfExists('fitness_interests'); 
    }
};
