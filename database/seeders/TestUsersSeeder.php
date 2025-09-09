<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
// No necesitamos importar FitnessInterest aquí, solo los IDs.
use Illuminate\Support\Facades\Hash;

class TestUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // --- Usuario en Plaza Mayor ---
        $userPlazaMayor = User::updateOrCreate(
            ['email' => 'user_plazamayor@example.com'],
            [
                'name' => 'Usuario en Plaza Mayor',
                'display_name' => 'Fan de la Plaza Mayor',
                'username' => 'user_plazamayor',
                'password' => Hash::make('password'),
                'location_city' => 'Madrid',
                'latitude' => 40.41538,
                'longitude' => -3.70734,
                'looking_for_interest_id' => 3, // Busca CrossFit
            ]
        );
        // Le asignamos intereses generales: CrossFit (3) y Running (6)
        $userPlazaMayor->fitnessInterests()->sync([3, 6]);

        // --- Usuario en Puerta del Sol ---
        $userSol = User::updateOrCreate(
            ['email' => 'user_sol@example.com'],
            [
                'name' => 'Usuario en Puerta del Sol',
                'display_name' => 'Corredor de Sol',
                'username' => 'user_sol',
                'password' => Hash::make('password'),
                'location_city' => 'Madrid',
                'latitude' => 40.41678,
                'longitude' => -3.70379,
                'looking_for_interest_id' => 6, // Busca Running
            ]
        );
        // Le asignamos intereses generales: Running (6), Yoga (4), Calistenia (2)
        $userSol->fitnessInterests()->sync([6, 4, 2]);
        
        // --- Usuario Cerca del Bernabéu ---
        $userBernabeu = User::updateOrCreate(
            ['email' => 'user_bernabeu@example.com'],
            [
                'name' => 'Usuario Cerca del Bernabéu',
                'display_name' => 'Forofo del Bernabéu',
                'username' => 'user_bernabeu',
                'password' => Hash::make('password'),
                'location_city' => 'Madrid',
                'latitude' => 40.45305,
                'longitude' => -3.68834,
                'looking_for_interest_id' => 1, // Busca Levantamiento de Pesas
            ]
        );
        // Le asignamos intereses generales: Levantamiento de Pesas (1) y CrossFit (3)
        $userBernabeu->fitnessInterests()->sync([1, 3]);

        // --- Usuario Lejano en Barcelona (para probar filtros de ciudad) ---
        User::updateOrCreate(
            ['email' => 'user_barcelona@example.com'],
            [
                'name' => 'Usuario Lejano en Barcelona',
                'display_name' => 'Playas de Barna',
                'username' => 'user_barcelona',
                'password' => Hash::make('password'),
                'location_city' => 'Barcelona',
                'latitude' => 41.38506,
                'longitude' => 2.17340,
                'looking_for_interest_id' => 8, // Busca Natación
            ]
        );
        // A este no le asignamos intereses generales para tener un caso de prueba diferente
        
        // --- Usuario Sin Coordenadas ---
        User::updateOrCreate(
            ['email' => 'user_nogps@example.com'],
            [
                'name' => 'Usuario Sin Coordenadas',
                'display_name' => 'Usuario sin GPS',
                'username' => 'user_nogps',
                'password' => Hash::make('password'),
                'location_city' => 'Madrid',
                'latitude' => null,
                'longitude' => null,
            ]
        );
    }
}