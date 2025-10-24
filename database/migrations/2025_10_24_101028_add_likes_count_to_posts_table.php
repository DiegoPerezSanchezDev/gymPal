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
        Schema::table('posts', function (Blueprint $table) {
            $table->integer('likes_count')->default(0)->after('image_path');
        });
        
        // Actualizar el contador para posts existentes
        \DB::statement('
            UPDATE posts 
            SET likes_count = (
                SELECT COUNT(*) 
                FROM post_like 
                WHERE post_like.post_id = posts.id
            )
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('likes_count');
        });
    }
};
