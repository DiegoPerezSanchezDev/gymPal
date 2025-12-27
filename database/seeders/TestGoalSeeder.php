<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Goal;
use App\Models\User;
use App\Models\WorkoutLog;
use Carbon\Carbon;

class TestGoalSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'diegito866@gmail.com')->first();
        if (!$user) {
            $user = User::first();
        }

        if (!$user) {
            $this->command->error("No se encontró ningún usuario para seedear metas.");
            return;
        }

        $this->command->info("Seedeando metas para: " . $user->email);

        // Limpiar para el test
        Goal::where('user_id', $user->id)->delete();
        
        // --- OBJETIVOS ACTIVOS (2) ---
        
        // 1. Entrenos semanales (3) - Progreso: 1/3
        Goal::create([
            'user_id' => $user->id,
            'type' => 'weekly_workouts',
            'target_value' => 3,
            'status' => 'active',
            'start_date' => Carbon::now()->startOfWeek(),
        ]);

        // 2. Volumen mensual (50k) - Progreso: ~12k
        Goal::create([
            'user_id' => $user->id,
            'type' => 'monthly_volume',
            'target_value' => 50000,
            'status' => 'active',
            'start_date' => Carbon::now()->startOfMonth(),
        ]);

        // --- OBJETIVOS COMPLETADOS (2) ---
        
        // 1. Madrugador (2 sesiones antes de 9AM)
        Goal::create([
            'user_id' => $user->id,
            'type' => 'early_bird',
            'target_value' => 2,
            'status' => 'completed',
            'start_date' => Carbon::now()->subWeek()->startOfWeek(),
            'end_date' => Carbon::now()->subWeek()->endOfWeek(),
        ]);

        // 2. Racha (7 días)
        Goal::create([
            'user_id' => $user->id,
            'type' => 'streak',
            'target_value' => 7,
            'status' => 'completed',
            'start_date' => Carbon::now()->subMonths(2),
            'end_date' => Carbon::now()->subMonths(2)->addDays(7),
        ]);

        // --- DATOS PARA EL PROGRESO ---
        
        // Log de ayer (para volumen y entreno semanal)
        WorkoutLog::create([
            'user_id' => $user->id,
            'workout_name' => 'Push Day de Fuerza',
            'exercises_data' => [
                [
                    'name' => 'Bench Press', 
                    'sets' => [
                        ['reps' => 10, 'weight' => 80, 'completed' => true],
                        ['reps' => 10, 'weight' => 80, 'completed' => true],
                        ['reps' => 10, 'weight' => 80, 'completed' => true],
                    ]
                ],
                [
                    'name' => 'Military Press', 
                    'sets' => [
                        ['reps' => 12, 'weight' => 40, 'completed' => true],
                        ['reps' => 12, 'weight' => 40, 'completed' => true],
                    ]
                ]
            ],
            'total_sets' => 5,
            'completed_sets' => 5,
            'created_at' => Carbon::now()->subDay()->setHour(10), // No es early bird
        ]);

        // Log de hoy temprano (para volumen y entreno semanal)
        WorkoutLog::create([
            'user_id' => $user->id,
            'workout_name' => 'Morning Pull',
            'exercises_data' => [
                [
                    'name' => 'Deadlift', 
                    'sets' => [
                        ['reps' => 5, 'weight' => 120, 'completed' => true],
                        ['reps' => 5, 'weight' => 120, 'completed' => true],
                        ['reps' => 5, 'weight' => 120, 'completed' => true],
                    ]
                ]
            ],
            'total_sets' => 3,
            'completed_sets' => 3,
            'created_at' => Carbon::now()->setHour(7), // Es early bird pero no estamos rastreando ese activo
        ]);

        $this->command->info("¡Seed completado con éxito! Revisa la página de Stats.");
    }
}
