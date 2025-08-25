<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::create('fitness_interest_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('fitness_interest_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->unique(['user_id', 'fitness_interest_id']);
        });
    }
    public function down(): void { 
        Schema::dropIfExists('fitness_interest_user'); 
    }
};
