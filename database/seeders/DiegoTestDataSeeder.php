<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Workout;
use App\Models\WorkoutExercise;
use App\Models\WorkoutLog;
use App\Models\Post;
use Carbon\Carbon;

class DiegoTestDataSeeder extends Seeder
{
    public function run()
    {
        // Buscar usuario diegop o crearlo
        $diego = User::where('username', 'diegop')->first();
        
        if (!$diego) {
            $this->command->warn('Usuario @diegop no encontrado. Creando usuario...');
            $diego = User::create([
                'name' => 'Diego Perez',
                'username' => 'diegop',
                'email' => 'diego@gympal.com',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
                'experience_level' => 'Avanzado',
                'location_city' => 'Madrid',
                'bio' => '🚀 Building GymPal | 🏋️ Calisthenics & Gym',
            ]);
        }

        $this->command->info('Generando datos de prueba para: ' . $diego->name . ' (@' . $diego->username . ')');

        // 1. Crear rutinas públicas
        $workouts = [
            [
                'name' => 'Full Body Strength',
                'description' => 'Rutina completa para ganar fuerza en todo el cuerpo',
                'category' => 'Fuerza',
                'difficulty' => 'Intermedio',
                'duration_minutes' => 60,
                'is_public' => true,
                'exercises' => [
                    ['name' => 'Sentadillas', 'sets' => 4, 'reps' => 10, 'weight' => 80],
                    ['name' => 'Press Banca', 'sets' => 4, 'reps' => 8, 'weight' => 70],
                    ['name' => 'Peso Muerto', 'sets' => 3, 'reps' => 6, 'weight' => 100],
                    ['name' => 'Dominadas', 'sets' => 3, 'reps' => 12, 'weight' => 0],
                ]
            ],
            [
                'name' => 'Cardio HIIT 30min',
                'description' => 'Entrenamiento de alta intensidad para quemar grasa',
                'category' => 'Cardio',
                'difficulty' => 'Avanzado',
                'duration_minutes' => 30,
                'is_public' => true,
                'exercises' => [
                    ['name' => 'Burpees', 'sets' => 5, 'reps' => 15, 'weight' => 0],
                    ['name' => 'Mountain Climbers', 'sets' => 5, 'reps' => 30, 'weight' => 0],
                    ['name' => 'Jump Squats', 'sets' => 4, 'reps' => 20, 'weight' => 0],
                ]
            ],
            [
                'name' => 'Upper Body Push',
                'description' => 'Enfoque en pecho, hombros y tríceps',
                'category' => 'Fuerza',
                'difficulty' => 'Intermedio',
                'duration_minutes' => 45,
                'is_public' => true,
                'exercises' => [
                    ['name' => 'Press Banca Inclinado', 'sets' => 4, 'reps' => 10, 'weight' => 60],
                    ['name' => 'Press Militar', 'sets' => 4, 'reps' => 8, 'weight' => 50],
                    ['name' => 'Fondos', 'sets' => 3, 'reps' => 12, 'weight' => 0],
                    ['name' => 'Aperturas con Mancuernas', 'sets' => 3, 'reps' => 12, 'weight' => 20],
                ]
            ],
            [
                'name' => 'Pierna Completa',
                'description' => 'Rutina intensa para desarrollar las piernas',
                'category' => 'Fuerza',
                'difficulty' => 'Avanzado',
                'duration_minutes' => 50,
                'is_public' => true,
                'exercises' => [
                    ['name' => 'Sentadilla Frontal', 'sets' => 4, 'reps' => 8, 'weight' => 70],
                    ['name' => 'Peso Muerto Rumano', 'sets' => 4, 'reps' => 10, 'weight' => 80],
                    ['name' => 'Zancadas', 'sets' => 3, 'reps' => 12, 'weight' => 30],
                    ['name' => 'Curl Femoral', 'sets' => 3, 'reps' => 15, 'weight' => 40],
                ]
            ],
            [
                'name' => 'Core & Abs Killer',
                'description' => 'Rutina enfocada en abdominales y core',
                'category' => 'Funcional',
                'difficulty' => 'Principiante',
                'duration_minutes' => 20,
                'is_public' => true,
                'exercises' => [
                    ['name' => 'Plancha', 'sets' => 3, 'reps' => 60, 'weight' => 0],
                    ['name' => 'Crunches', 'sets' => 4, 'reps' => 25, 'weight' => 0],
                    ['name' => 'Russian Twists', 'sets' => 3, 'reps' => 30, 'weight' => 10],
                    ['name' => 'Leg Raises', 'sets' => 3, 'reps' => 15, 'weight' => 0],
                ]
            ],
        ];

        foreach ($workouts as $workoutData) {
            $exercises = $workoutData['exercises'];
            unset($workoutData['exercises']);

            $workout = Workout::create([
                'user_id' => $diego->id,
                ...$workoutData,
                'times_saved' => rand(5, 50),
            ]);

            foreach ($exercises as $index => $exercise) {
                // Crear array de sets (sets_data)
                $setsData = [];
                for ($i = 0; $i < $exercise['sets']; $i++) {
                    $setsData[] = [
                        'reps' => $exercise['reps'],
                        'weight' => $exercise['weight'],
                        'type' => 'normal'
                    ];
                }

                WorkoutExercise::create([
                    'workout_id' => $workout->id,
                    'exercise_name' => $exercise['name'],
                    'sets_data' => $setsData,
                    'rest_seconds' => 60,
                    'order' => $index + 1,
                    'notes' => null,
                ]);
            }

            $this->command->info("✓ Rutina creada: {$workout->name}");
        }

        // 2. Crear workout logs (últimas 8 semanas)
        $this->command->info('Generando historial de entrenamientos...');
        
        for ($week = 0; $week < 8; $week++) {
            $workoutsThisWeek = rand(2, 5); // 2-5 entrenamientos por semana
            
            for ($i = 0; $i < $workoutsThisWeek; $i++) {
                $randomWorkout = Workout::where('user_id', $diego->id)->inRandomOrder()->first();
                $daysAgo = ($week * 7) + rand(0, 6);
                
                // Construir exercises_data para el log
                $exercisesData = [];
                foreach ($randomWorkout->exercises as $ex) {
                    $sets = [];
                    if (is_array($ex->sets_data)) {
                        foreach ($ex->sets_data as $set) {
                            $sets[] = [
                                'reps' => $set['reps'],
                                'weight' => $set['weight'],
                                'completed' => true
                            ];
                        }
                    }
                    $exercisesData[] = [
                        'name' => $ex->exercise_name,
                        'sets' => $sets
                    ];
                }

                WorkoutLog::create([
                    'user_id' => $diego->id,
                    'workout_id' => $randomWorkout->id,
                    'workout_name' => $randomWorkout->name,
                    'exercises_data' => $exercisesData,
                    'duration_minutes' => $randomWorkout->duration_minutes + rand(-5, 10),
                    'total_sets' => count($exercisesData) * 3, // Aproximado
                    'completed_sets' => count($exercisesData) * 3,
                    'created_at' => Carbon::now()->subDays($daysAgo),
                    'updated_at' => Carbon::now()->subDays($daysAgo),
                ]);
            }
        }

        $this->command->info('✓ Historial de entrenamientos generado');
        $this->command->info('');
        $this->command->info('🎉 Datos de prueba generados exitosamente para @diegop!');
        $this->command->info('   - 5 rutinas públicas');
        $this->command->info('   - ~30 entrenamientos registrados (últimas 8 semanas)');
    }
}
