<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gyms', function (Blueprint $table) {
            $table->id();
            $table->string('external_id')->nullable()->index(); // OSM ID
            $table->string('name');
            $table->decimal('latitude', 10, 8)->index();
            $table->decimal('longitude', 11, 8)->index();
            $table->string('type')->default('gym'); // gym, crossfit, yoga, park, pool
            $table->string('address')->nullable();
            $table->string('city')->nullable()->index();
            $table->string('website')->nullable();
            $table->decimal('rating', 3, 2)->nullable();
            $table->integer('users_count')->default(0);
            $table->json('meta_data')->nullable(); // Horarios, facilities, etc
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gyms');
    }
};
