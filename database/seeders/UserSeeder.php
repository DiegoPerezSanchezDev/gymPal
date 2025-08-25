<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Diego Pérez',
                'username' => 'diegop',
                'display_name' => 'Diego',
                'email' => 'diegito866@gmail.com',
                'location_city' => 'Madrid',
                'bio' => 'Me gusta el deporte y conocer gente nueva.',
                'experience_level' => 'Intermedio',
                'availability_general' => ['Mañanas', 'Fines de semana'],
            ],
            [
                'name' => 'Laura Gómez',
                'username' => 'laurag',
                'display_name' => 'Laura',
                'email' => 'laura@gympal.com',
                'location_city' => 'Barcelona',
                'bio' => 'Yoga y running son mi pasión.',
                'experience_level' => 'Principiante',
                'availability_general' => ['Tardes', 'Fines de semana'],
            ],
            [
                'name' => 'Carlos Ruiz',
                'username' => 'carlosr',
                'display_name' => 'Carlos',
                'email' => 'carlos@gympal.com',
                'location_city' => 'Valencia',
                'bio' => 'Crossfit y pesas.',
                'experience_level' => 'Avanzado',
                'availability_general' => ['Noches', 'Fines de semana'],
            ],
            [
                'name' => 'Ana López',
                'username' => 'analo',
                'display_name' => 'Ana',
                'email' => 'ana@gympal.com',
                'location_city' => 'Sevilla',
                'bio' => 'Pilates y natación.',
                'experience_level' => 'Intermedio',
                'availability_general' => ['Mañanas', 'Tardes'],
            ],
            [
                'name' => 'Pedro Sánchez',
                'username' => 'pedros',
                'display_name' => 'Pedro',
                'email' => 'pedro@gympal.com',
                'location_city' => 'Bilbao',
                'bio' => 'Senderismo y ciclismo.',
                'experience_level' => 'Principiante',
                'availability_general' => ['Tardes', 'Noches'],
            ],
        ];

        foreach ($users as $data) {
            User::updateOrCreate(
                ['email' => $data['email']],
                array_merge($data, ['password' => Hash::make('password')])
            );
        }
    }
}
