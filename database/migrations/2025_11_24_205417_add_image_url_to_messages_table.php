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
            // Añadir columna para URL de imagen
            $table->string('image_url')->nullable()->after('body');
            
            // Hacer body nullable para permitir mensajes solo con imagen
            $table->text('body')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn('image_url');
            
            // Revertir body a NOT NULL (si es necesario)
            $table->text('body')->nullable(false)->change();
        });
    }
};
