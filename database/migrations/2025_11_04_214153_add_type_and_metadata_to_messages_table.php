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
        Schema::table('messages', function (Blueprint $table) {
            // Tipo de mensaje: 'text' para mensajes normales, 'shared_post' para posts compartidos
            $table->string('type')->default('text')->after('body');
            
            // Metadata en formato JSON para almacenar información adicional (ej: datos del post compartido)
            $table->json('metadata')->nullable()->after('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn(['type', 'metadata']);
        });
    }
};
