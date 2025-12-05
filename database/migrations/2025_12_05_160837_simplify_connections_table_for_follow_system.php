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
        Schema::table('connections', function (Blueprint $table) {
            // Eliminar la columna status - ahora es simplemente "A conecta con B"
            $table->dropColumn('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('connections', function (Blueprint $table) {
            // Restaurar la columna status si se hace rollback
            $table->enum('status', ['pending', 'accepted', 'rejected', 'blocked'])->default('pending');
        });
    }
};
