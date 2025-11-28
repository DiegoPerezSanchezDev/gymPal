<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Workout;
use App\Models\WorkoutLog;
use Carbon\Carbon;

class WorkoutLogSeeder extends Seeder
{
    public function run(): void
    {
        $diego = User::where('username', 'diegop')->first();
        $laura = User::where('username', 'user_sol')->first();

        // Si no existen esos usuarios, usar los de TestUsersSeeder
        if (!$diego) {
            $diego = User::where('username', 'user_plazamayor')->first();
        }
        if (!$laura) {
            $laura = User::where('username', 'user_sol')->first();
        }
        if (!$elena) {
            $elena = User::where('username', 'user_bernabeu')->first();
        }

        if (!$diego || !$laura || !$elena) {
            $this->command->warn('⚠️  Usuarios no encontrados. Saltando WorkoutLogSeeder.');
            return;
        }

        $pushDay = Workout::where('name', 'Push Day - Torso Empuje')->first();
        $pullDay = Workout::where('name', 'Pull Day - Espalda y Bíceps')->first();
        $legDay = Workout::where('name', 'Leg Day - Pierna Completa')->first();
        $upperBody = Workout::where('name', 'Upper Body Strength')->first();
        $wod = Workout::where('name', 'Cindy WOD')->first();

        // Diego's workout logs (last 2 weeks)
        
        // Push Day - 5 days ago
        WorkoutLog::create([
            'user_id' => $diego->id,
            'workout_id' => $pushDay->id,
            'workout_name' => $pushDay->name,
            'exercises_data' => [
                [
                    'name' => 'Press Banca Plano',
                    'sets' => [
                        ['reps' => 8, 'weight' => 80, 'completed' => true],
                        ['reps' => 7, 'weight' => 80, 'completed' => true],
                        ['reps' => 6, 'weight' => 80, 'completed' => true],
                        ['reps' => 6, 'weight' => 80, 'completed' => true],
                    ]
                ],
                [
                    'name' => 'Press Inclinado con Mancuernas',
                    'sets' => [
                        ['reps' => 10, 'weight' => 32, 'completed' => true],
                        ['reps' => 9, 'weight' => 32, 'completed' => true],
                        ['reps' => 8, 'weight' => 32, 'completed' => true],
                        ['reps' => 8, 'weight' => 32, 'completed' => true],
                    ]
                ],
                [
                    'name' => 'Aperturas con Mancuernas',
                    'sets' => [
                        ['reps' => 12, 'weight' => 16, 'completed' => true],
                        ['reps' => 12, 'weight' => 16, 'completed' => true],
                        ['reps' => 12, 'weight' => 16, 'completed' => true],
                    ]
                ]
            ],
            'total_sets' => 11,
            'completed_sets' => 11,
            'duration_minutes' => 78,
            'notes' => 'Buen entrenamiento. Sensaciones muy buenas en press banca.',
            'created_at' => Carbon::now()->subDays(5),
            'updated_at' => Carbon::now()->subDays(5),
        ]);

        // Pull Day - 3 days ago
        WorkoutLog::create([
            'user_id' => $diego->id,
            'workout_id' => $pullDay->id,
            'workout_name' => $pullDay->name,
            'exercises_data' => [
                [
                    'name' => 'Dominadas Pronas',
                    'sets' => [
                        ['reps' => 10, 'weight' => 0, 'completed' => true],
                        ['reps' => 9, 'weight' => 0, 'completed' => true],
                        ['reps' => 8, 'weight' => 0, 'completed' => true],
                        ['reps' => 8, 'weight' => 0, 'completed' => true],
                    ]
                ],
                [
                    'name' => 'Remo con Barra',
                    'sets' => [
                        ['reps' => 10, 'weight' => 70, 'completed' => true],
                        ['reps' => 10, 'weight' => 70, 'completed' => true],
                        ['reps' => 8, 'weight' => 70, 'completed' => true],
                        ['reps' => 8, 'weight' => 70, 'completed' => true],
                    ]
                ]
            ],
            'total_sets' => 8,
            'completed_sets' => 8,
            'duration_minutes' => 72,
            'notes' => 'Espalda congestionada. Nuevo PR en dominadas!',
            'created_at' => Carbon::now()->subDays(3),
            'updated_at' => Carbon::now()->subDays(3),
        ]);

        // Leg Day - 1 day ago
        WorkoutLog::create([
            'user_id' => $diego->id,
            'workout_id' => $legDay->id,
            'workout_name' => $legDay->name,
            'exercises_data' => [
                [
                    'name' => 'Sentadilla con Barra',
                    'sets' => [
                        ['reps' => 8, 'weight' => 100, 'completed' => true],
                        ['reps' => 7, 'weight' => 100, 'completed' => true],
                        ['reps' => 6, 'weight' => 100, 'completed' => true],
                        ['reps' => 6, 'weight' => 100, 'completed' => true],
                        ['reps' => 6, 'weight' => 100, 'completed' => true],
                    ]
                ],
                [
                    'name' => 'Prensa de Pierna',
                    'sets' => [
                        ['reps' => 12, 'weight' => 180, 'completed' => true],
                        ['reps' => 12, 'weight' => 180, 'completed' => true],
                        ['reps' => 10, 'weight' => 180, 'completed' => true],
                        ['reps' => 10, 'weight' => 180, 'completed' => true],
                    ]
                ]
            ],
            'total_sets' => 9,
            'completed_sets' => 9,
            'duration_minutes' => 95,
            'notes' => 'Día duro pero productivo. Las piernas van a doler mañana.',
            'created_at' => Carbon::now()->subDays(1),
            'updated_at' => Carbon::now()->subDays(1),
        ]);

        // Laura's workout logs
        WorkoutLog::create([
            'user_id' => $laura->id,
            'workout_id' => $upperBody->id,
            'workout_name' => $upperBody->name,
            'exercises_data' => [
                [
                    'name' => 'Press Banca con Mancuernas',
                    'sets' => [
                        ['reps' => 10, 'weight' => 16, 'completed' => true],
                        ['reps' => 9, 'weight' => 16, 'completed' => true],
                        ['reps' => 8, 'weight' => 16, 'completed' => true],
                        ['reps' => 8, 'weight' => 16, 'completed' => true],
                    ]
                ]
            ],
            'total_sets' => 4,
            'completed_sets' => 4,
            'duration_minutes' => 62,
            'notes' => 'Excelente sesión con mis clientas hoy.',
            'created_at' => Carbon::now()->subDays(2),
            'updated_at' => Carbon::now()->subDays(2),
        ]);

        // Elena's Crossfit WOD logs
        WorkoutLog::create([
            'user_id' => $elena->id,
            'workout_id' => $wod->id,
            'workout_name' => $wod->name,
            'exercises_data' => [
                [
                    'name' => 'Pull-ups',
                    'sets' => [
                        ['reps' => 5, 'weight' => 0, 'completed' => true]
                    ]
                ],
                [
                    'name' => 'Push-ups',
                    'sets' => [
                        ['reps' => 10, 'weight' => 0, 'completed' => true]
                    ]
                ],
                [
                    'name' => 'Air Squats',
                    'sets' => [
                        ['reps' => 15, 'weight' => 0, 'completed' => true]
                    ]
                ]
            ],
            'total_sets' => 3,
            'completed_sets' => 3,
            'duration_minutes' => 20,
            'notes' => '15 rondas completadas! Nuevo PR personal.',
            'created_at' => Carbon::now()->subDays(1),
            'updated_at' => Carbon::now()->subDays(1),
        ]);

        $this->command->info('✅ Workout logs created successfully!');
    }
}
