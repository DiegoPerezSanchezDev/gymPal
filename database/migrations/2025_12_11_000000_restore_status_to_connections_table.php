<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('connections', function (Blueprint $table) {
            // Restaurar la columna status
            $table->enum('status', ['pending', 'accepted', 'rejected', 'blocked'])->default('pending')->after('receiver_id');
        });

        // Actualizar todas las conexiones existentes a 'accepted' para mantener las amistades actuales
        DB::table('connections')->update(['status' => 'accepted']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('connections', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
