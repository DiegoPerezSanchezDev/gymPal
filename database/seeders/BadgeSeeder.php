<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Badge;

class BadgeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $badges = [
            // --- PRUEBA ---
            [
                'slug' => 'tester_master',
                'name' => 'Tester Master',
                'description' => 'Badge de prueba para verificar el sistema',
                'icon' => '🧪',
                'target_value' => 1, // Con 1 entrenamiento salta
                'category' => 'test'
            ],
            // --- GENERAL ---
            [
                'slug' => 'first_steps',
                'name' => 'Primeros Pasos',
                'description' => 'Completa tu primer entrenamiento',
                'icon' => '🚀',
                'target_value' => 1,
                'category' => 'general'
            ],
            [
                'slug' => 'gym_rat',
                'name' => 'Gym Rat',
                'description' => '5 entrenamientos en una semana',
                'icon' => '🐀',
                'target_value' => 5,
                'category' => 'frequency'
            ],
            [
                'slug' => 'on_fire',
                'name' => 'En Racha',
                'description' => 'Racha de 3 días seguidos',
                'icon' => '🔥',
                'target_value' => 3,
                'category' => 'consistency'
            ],
            [
                'slug' => 'iron_habit',
                'name' => 'Hábito de Hierro',
                'description' => 'Entrena 30 días distintos',
                'icon' => '🛡️',
                'target_value' => 30,
                'category' => 'consistency'
            ],
            [
                'slug' => 'dedication',
                'name' => 'Dedicación',
                'description' => '100 entrenamientos totales',
                'icon' => '💪',
                'target_value' => 100,
                'category' => 'consistency'
            ],
            // --- DURACIÓN ---
            [
                'slug' => 'marathon_runner',
                'name' => 'Maratoniano',
                'description' => 'Entreno de +90 minutos',
                'icon' => '🏃',
                'target_value' => 90,
                'category' => 'duration'
            ],
            // --- VARIEDAD ---
            [
                'slug' => 'variety_master',
                'name' => 'Variedad Total',
                'description' => '20 ejercicios diferentes',
                'icon' => '🎨',
                'target_value' => 20,
                'category' => 'variety'
            ],
            // --- VOLUMEN ---
            [
                'slug' => 'human_crane',
                'name' => 'Grúa Humana',
                'description' => 'Mueve 10,000 kg en total',
                'icon' => '🚜',
                'target_value' => 10000,
                'category' => 'volume'
            ],
            [
                'slug' => 'hercules',
                'name' => 'Hércules',
                'description' => 'Mueve 100,000 kg en total',
                'icon' => '✈️',
                'target_value' => 100000,
                'category' => 'volume'
            ],
            [
                'slug' => 'volume_king',
                'name' => 'Rey del Volumen',
                'description' => 'Mueve 500,000 kg en total',
                'icon' => '👑',
                'target_value' => 500000,
                'category' => 'volume'
            ],
            [
                'slug' => 'atlas',
                'name' => 'Atlas',
                'description' => 'Mueve 1,000,000 kg en total',
                'icon' => '🌍',
                'target_value' => 1000000,
                'category' => 'volume'
            ],
            // --- HORARIOS ---
            [
                'slug' => 'early_bird',
                'name' => 'Early Bird',
                'description' => 'Entrena antes de las 8 AM',
                'icon' => '🐦',
                'target_value' => 1,
                'category' => 'schedule'
            ],
            [
                'slug' => 'night_owl',
                'name' => 'Noctámbulo',
                'description' => 'Entrena después de las 8 PM',
                'icon' => '🦉',
                'target_value' => 1,
                'category' => 'schedule'
            ],
            [
                'slug' => 'weekend_warrior',
                'name' => 'Finde Guerrero',
                'description' => '10 entrenos en fin de semana',
                'icon' => '📅',
                'target_value' => 10,
                'category' => 'schedule'
            ],
            // --- FUERZA ---
            [
                'slug' => 'heavy_weight',
                'name' => 'Peso Pesado',
                'description' => 'Levanta 60kg en un ejercicio',
                'icon' => '🏋️',
                'target_value' => 60,
                'category' => 'strength'
            ],
            [
                'slug' => 'beast_mode',
                'name' => 'Bestia Pura',
                'description' => 'Levanta 100kg en un ejercicio',
                'icon' => '🦍',
                'target_value' => 100,
                'category' => 'strength'
            ],
        ];

        foreach ($badges as $badge) {
            Badge::updateOrCreate(['slug' => $badge['slug']], $badge);
        }
    }
}
