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
        Schema::table('stories', function (Blueprint $table) {
            $table->string('type')->default('standard')->after('image_path'); // standard, workout_data, challenge, ghost_mode
            $table->json('metadata')->nullable()->after('type'); // Datos flexibles
            $table->foreignId('workout_log_id')->nullable()->constrained()->onDelete('set null')->after('user_id'); // Link directo (opcional)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stories', function (Blueprint $table) {
            $table->dropForeign(['workout_log_id']);
            $table->dropColumn(['workout_log_id', 'type', 'metadata']);
        });
    }
};
