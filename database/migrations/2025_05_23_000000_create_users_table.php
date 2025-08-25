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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Solo intenta eliminar columnas si existen para evitar errores en rollback
            $columnsToDrop = [];
            if (Schema::hasColumn('users', 'username')) $columnsToDrop[] = 'username';
            if (Schema::hasColumn('users', 'display_name')) $columnsToDrop[] = 'display_name';
            if (Schema::hasColumn('users', 'profile_picture_url')) $columnsToDrop[] = 'profile_picture_url';
            if (Schema::hasColumn('users', 'bio')) $columnsToDrop[] = 'bio';
            if (Schema::hasColumn('users', 'location_city')) $columnsToDrop[] = 'location_city';
            if (Schema::hasColumn('users', 'availability_general')) $columnsToDrop[] = 'availability_general';
            if (Schema::hasColumn('users', 'experience_level')) $columnsToDrop[] = 'experience_level';
            if (Schema::hasColumn('users', 'fitness_interests')) $columnsToDrop[] = 'fitness_interests';

            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
