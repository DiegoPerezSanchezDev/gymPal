<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // BLOQUE 1: Seeders de datos base
        $this->call([
            FitnessInterestSeeder::class,
        ]);

        // BLOQUE 2: Usuarios de prueba
        $this->call([
            TestUsersSeeder::class,
            UserSeeder::class,
            DiegoTestDataSeeder::class,
        ]);

        // BLOQUE 3: Relaciones
        $this->call([
            FitnessInterestUserSeeder::class,
            FollowerSeeder::class,
            ConversationSeeder::class,
        ]);

        // BLOQUE 4: Mensajes
        $this->call([
            MessageSeeder::class,
        ]);

        // BLOQUE 5: Rutinas y Logs
        $this->call([
            WorkoutSeeder::class,
            LauraWorkoutSeeder::class,
            WorkoutLogSeeder::class,
        ]);

        // BLOQUE 6: Conexiones
        $this->call([
            ConnectionSeeder::class,
        ]);
    }
}
