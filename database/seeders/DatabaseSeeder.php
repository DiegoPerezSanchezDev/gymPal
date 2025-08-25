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
        // BLOQUE 1: Seeders de datos base (no tienen dependencias)
        // -----------------------------------------------------------------
        // Primero, creamos las entidades fundamentales: Usuarios e Intereses.
        $this->call([
            UserSeeder::class,
            FitnessInterestSeeder::class,
        ]);

        // BLOQUE 2: Seeders de relaciones (dependen del bloque 1)
        // -----------------------------------------------------------------
        // Ahora que ya existen usuarios e intereses, podemos crear
        // las relaciones entre ellos.
        $this->call([
            FitnessInterestUserSeeder::class, // Necesita Users y FitnessInterests
            FollowerSeeder::class,           // Necesita Users
            ConversationSeeder::class,       // Necesita Users
        ]);

        // BLOQUE 3: Seeders que dependen del bloque 2
        // -----------------------------------------------------------------
        // Finalmente, como ya hemos creado las conversaciones, podemos
        // llenarlas con mensajes.
        $this->call([
            MessageSeeder::class,            // Necesita Conversations
        ]);
        
    }
}