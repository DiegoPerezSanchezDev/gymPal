<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WorkoutLog;
use App\Models\Category;
use App\Models\User;
use Carbon\Carbon;

class TestWorkoutStatsSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'diegito866@gmail.com')->first() ?: User::first();
        if (!$user) return;

        $categories = Category::all();
        $this->command->info("Generando datos de prueba para el usuario: {$user->name}");

        // Limpiar logs previos si quieres una prueba limpia (opcional)
        // WorkoutLog::where('user_id', $user->id)->delete();

        // 1. Logs para esta semana (para probar las Metas Semanales)
        $thisWeek = [
            ['name' => 'Empuje Pesado', 'cat' => 'fuerza', 'days_ago' => 0],
            ['name' => 'Circuito Funcional', 'cat' => 'funcional', 'days_ago' => 1],
            ['name' => 'Sesión de Yoga', 'cat' => 'yoga', 'days_ago' => 2],
            ['name' => 'Fullbody Gym', 'cat' => 'gimnasio', 'days_ago' => 3],
        ];

        foreach ($thisWeek as $data) {
            $cat = $categories->where('slug', $data['cat'])->first();
            WorkoutLog::create([
                'user_id' => $user->id,
                'workout_name' => $data['name'],
                'category_id' => $cat ? $cat->id : null,
                'created_at' => Carbon::now()->subDays($data['days_ago'])->setHour(10),
                'exercises_data' => [
                    [
                        'exercise' => 'Ejercicio Test',
                        'sets' => [
                            ['reps' => 10, 'weight' => 50, 'completed' => true],
                            ['reps' => 10, 'weight' => 55, 'completed' => true],
                        ]
                    ],
                    ['exercise' => 'Core Mix', 'sets' => [['reps' => 20, 'completed' => true]]]
                ]
            ]);
        }

        // 2. Logs históricos para rellenar el Donut Chart
        $historical = [
            ['name' => 'Running 5km', 'cat' => 'cardio', 'date' => Carbon::now()->subMonths(1)],
            ['name' => 'Crossfit WOD', 'cat' => 'crossfit', 'date' => Carbon::now()->subWeeks(2)],
            ['name' => 'Calistenia Park', 'cat' => 'calistenia', 'date' => Carbon::now()->subDays(15)],
            ['name' => 'Partido Fútbol', 'cat' => 'deportes', 'date' => Carbon::now()->subDays(20)],
        ];

        foreach ($historical as $data) {
            $cat = $categories->where('slug', $data['cat'])->first();
            WorkoutLog::create([
                'user_id' => $user->id,
                'workout_name' => $data['name'],
                'category_id' => $cat ? $cat->id : null,
                'created_at' => $data['date'],
                'exercises_data' => [['exercise' => 'General', 'sets' => [['reps' => 10, 'completed' => true]]]]
            ]);
        }

        $this->command->info("¡Listo! Se han añadido 8 entrenamientos de prueba con distintas categorías.");
    }
}
