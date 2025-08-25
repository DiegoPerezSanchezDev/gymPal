<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TestUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Array con los datos de nuestros usuarios de prueba.
        // Hemos cogido coordenadas reales de lugares conocidos.
        $testUsers = [
            [
                'name' => 'Usuario en Plaza Mayor',
                'display_name' => 'Fan de la Plaza Mayor',
                'username' => 'user_plazamayor',
                'email' => 'user_plazamayor@example.com',
                'location_city' => 'Madrid',
                'latitude' => 40.41538, // Muy cerca de nuestro punto de prueba
                'longitude' => -3.70734,
            ],
            [
                'name' => 'Usuario en Puerta del Sol',
                'display_name' => 'Corredor de Sol',
                'username' => 'user_sol',
                'email' => 'user_sol@example.com',
                'location_city' => 'Madrid',
                'latitude' => 40.41678, // Cerca
                'longitude' => -3.70379,
            ],
            [
                'name' => 'Usuario Cerca del Bernabéu',
                'display_name' => 'Forofo del Bernabéu',
                'username' => 'user_bernabeu',
                'email' => 'user_bernabeu@example.com',
                'location_city' => 'Madrid',
                'latitude' => 40.45305, // Más lejos, pero dentro del radio
                'longitude' => -3.68834,
            ],
            [
                'name' => 'Usuario Lejano en Barcelona',
                'display_name' => 'Playas de Barna',
                'username' => 'user_barcelona',
                'email' => 'user_barcelona@example.com',
                'location_city' => 'Barcelona',
                'latitude' => 41.38506, // Muy lejos, no debería aparecer
                'longitude' => 2.17340,
            ],
            [
                'name' => 'Usuario Sin Coordenadas',
                'display_name' => 'Usuario sin GPS',
                'username' => 'user_nogps',
                'email' => 'user_nogps@example.com',
                'location_city' => 'Madrid',
                'latitude' => null, // No debería aparecer en la búsqueda por GPS
                'longitude' => null,
            ],
        ];

        foreach ($testUsers as $userData) {
            // Usamos updateOrCreate para evitar duplicados si ejecutamos el seeder varias veces.
            User::updateOrCreate(
                ['email' => $userData['email']], // Clave única para buscar
                [ // Datos para crear o actualizar
                    'name' => $userData['name'],
                    'display_name' => $userData['display_name'],
                    'username' => $userData['username'],
                    'password' => Hash::make('password'), // Ponemos una contraseña simple
                    'location_city' => $userData['location_city'],
                    'latitude' => $userData['latitude'],
                    'longitude' => $userData['longitude'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}